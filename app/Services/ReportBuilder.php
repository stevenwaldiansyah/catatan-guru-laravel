<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Models\AttendanceRecord;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\Request;

class ReportBuilder
{
    /**
     * @return array{title:string, period:string, columns:list<string>, rows:list<list<string>>}
     */
    public function build(User $user, string $type, Request $request): array
    {
        return match ($type) {
            'jurnal' => $this->journals($user, $request),
            'absensi' => $this->attendance($user, $request),
            'catatan' => $this->notes($user, $request),
            default => abort(404),
        };
    }

    /**
     * @return array{title:string, period:string, columns:list<string>, rows:list<list<string>>}
     */
    private function journals(User $user, Request $request): array
    {
        $journals = TeachingJournal::query()
            ->visibleTo($user)
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')))
            ->when($user->isAdmin() && $request->filled('teacher_id'), fn ($query) => $query->where('teacher_id', $request->integer('teacher_id')))
            ->orderBy('date')
            ->get();

        return [
            'title' => 'Laporan Jurnal Mengajar',
            'period' => $this->period($request),
            'columns' => ['Tanggal', 'Guru', 'Kelas', 'Mapel', 'Sesi', 'Materi', 'Kegiatan', 'Metode', 'Catatan', 'Status'],
            'rows' => $journals->map(fn (TeachingJournal $journal) => [
                $journal->date->translatedFormat('d M Y'),
                $journal->teacher->name,
                $journal->schoolClass->name,
                $journal->subject->name,
                $journal->session_label,
                $journal->material,
                (string) $journal->activities,
                (string) $journal->method,
                (string) $journal->notes,
                $journal->status->label(),
            ])->all(),
        ];
    }

    /**
     * @return array{title:string, period:string, columns:list<string>, rows:list<list<string>>}
     */
    private function attendance(User $user, Request $request): array
    {
        $classId = $request->integer('class_id');
        $this->assertClassAccess($user, $classId);

        $students = Student::query()
            ->where('status', 'aktif')
            ->when($classId, fn ($query) => $query->whereHas('enrollments', fn ($enrollment) => $enrollment->where('class_id', $classId)->where('status', EnrollmentStatus::Active)))
            ->when(! $classId && $user->isTeacher(), function ($query) use ($user) {
                $ids = $user->teacher?->assignments()->pluck('class_id') ?? collect();
                $query->whereHas('enrollments', fn ($enrollment) => $enrollment->whereIn('class_id', $ids)->where('status', EnrollmentStatus::Active));
            })
            ->orderBy('name')
            ->get();

        $records = AttendanceRecord::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereHas('session', function ($query) use ($request, $user, $classId) {
                $query->visibleTo($user);
                if ($classId) {
                    $query->where('class_id', $classId);
                }
                if ($request->filled('subject_id')) {
                    $query->where('subject_id', $request->integer('subject_id'));
                }
                if ($request->filled('date_from')) {
                    $query->whereDate('date', '>=', $request->date('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('date', '<=', $request->date('date_to'));
                }
            })
            ->get()
            ->groupBy('student_id');

        return [
            'title' => 'Rekap Absensi Siswa',
            'period' => $this->period($request),
            'columns' => ['NIS', 'Nama', 'Hadir', 'Izin', 'Sakit', 'Alpa'],
            'rows' => $students->map(function (Student $student) use ($records) {
                $items = $records->get($student->id, collect());

                return [
                    (string) $student->nis,
                    $student->name,
                    (string) $items->where('status', AttendanceStatus::Present)->count(),
                    (string) $items->where('status', AttendanceStatus::Permitted)->count(),
                    (string) $items->where('status', AttendanceStatus::Sick)->count(),
                    (string) $items->where('status', AttendanceStatus::Absent)->count(),
                ];
            })->all(),
        ];
    }

    /**
     * @return array{title:string, period:string, columns:list<string>, rows:list<list<string>>}
     */
    private function notes(User $user, Request $request): array
    {
        $notes = StudentNote::query()
            ->visibleTo($user)
            ->with(['teacher', 'student', 'schoolClass'])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('student_id'), fn ($query) => $query->where('student_id', $request->integer('student_id')))
            ->orderBy('date')
            ->get();

        return [
            'title' => 'Laporan Catatan Perkembangan Siswa',
            'period' => $this->period($request),
            'columns' => ['Tanggal', 'Guru', 'Siswa', 'Kelas', 'Kategori', 'Deskripsi', 'Tindak lanjut'],
            'rows' => $notes->map(fn (StudentNote $note) => [
                $note->date->translatedFormat('d M Y'),
                $note->teacher->name,
                $note->student->name,
                $note->schoolClass->name,
                $note->categoryLabel(),
                $note->description,
                (string) $note->follow_up,
            ])->all(),
        ];
    }

    private function period(Request $request): string
    {
        $from = $request->string('date_from')->toString();
        $to = $request->string('date_to')->toString();
        if ($from && $to) {
            return $from.' s.d. '.$to;
        }
        if ($from) {
            return 'Mulai '.$from;
        }
        if ($to) {
            return 'Sampai '.$to;
        }

        return 'Semua periode';
    }

    private function assertClassAccess(User $user, int $classId): void
    {
        if (! $classId || $user->isAdmin()) {
            return;
        }

        $allowed = $user->teacher?->assignments()->pluck('class_id') ?? collect();
        abort_unless($allowed->contains($classId), 403);
        SchoolClass::query()->findOrFail($classId);
    }
}
