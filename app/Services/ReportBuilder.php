<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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

        $rows = $journals->values()->map(function (TeachingJournal $journal, int $index) {
            $session = trim((string) $journal->session_label);
            $date = $this->longDate($journal->date);
            $material = trim((string) $journal->material);
            $activities = trim((string) $journal->activities);
            if ($activities !== '') {
                $material = trim($material."\n\nKegiatan:\n".$activities);
            }
            $notes = trim((string) $journal->notes);

            return [
                (string) ($index + 1),
                $session !== '' ? $date."\n".$session : $date,
                $journal->schoolClass->name,
                trim($journal->subject->name."\n".$journal->teacher->name),
                $material !== '' ? $material : '-',
                $notes !== '' ? $notes : '-',
            ];
        })->all();

        return [
            'title' => 'Laporan Jurnal Mengajar',
            'heading' => 'LAPORAN JURNAL KEGIATAN PEMBELAJARAN GURU',
            'period' => $this->period($request),
            'meta' => [
                ['Guru Pengampu', $this->teacherLabel($user, $request)],
                ['Periode Tanggal', $this->period($request)],
                ['Tanggal Cetak', $this->longDate(now())],
                ['Total Jurnal', count($rows).' Catatan Kegiatan'],
            ],
            'columns' => ['No', 'Tanggal / Jam', 'Kelas', 'Mata Pelajaran', 'Materi & Kegiatan Pembelajaran', 'Kendala & Refleksi'],
            'widths' => ['6%', '14%', '12%', '16%', '28%', '24%'],
            'align' => ['center', 'left', 'left', 'left', 'left', 'left'],
            'rows' => $rows,
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
            ->with(['enrollments' => fn ($enrollment) => $enrollment->where('status', EnrollmentStatus::Active)->with('schoolClass')])
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

        $rows = $students->values()->map(function (Student $student, int $index) use ($records, $classId) {
            $items = $records->get($student->id, collect());
            $present = $items->where('status', AttendanceStatus::Present)->count();
            $permitted = $items->where('status', AttendanceStatus::Permitted)->count();
            $sick = $items->where('status', AttendanceStatus::Sick)->count();
            $absent = $items->where('status', AttendanceStatus::Absent)->count();

            return [
                (string) ($index + 1),
                (string) $student->nis,
                $student->name,
                $this->classLabel($student, $classId),
                (string) $present,
                (string) $permitted,
                (string) $sick,
                (string) $absent,
                $this->attendancePercent($present, $present + $permitted + $sick + $absent),
            ];
        })->all();

        $className = $classId
            ? (SchoolClass::query()->find($classId)?->name ?? 'Semua Kelas')
            : 'Semua Kelas';

        return [
            'title' => 'Rekap Absensi Siswa',
            'heading' => 'LAPORAN REKAP ABSENSI SISWA',
            'period' => $this->period($request),
            'meta' => [
                ['Kelas', $className],
                ['Periode Tanggal', $this->period($request)],
                ['Tanggal Cetak', $this->longDate(now())],
                ['Total Siswa', count($rows).' Siswa'],
            ],
            'columns' => ['No', 'NIS', 'Nama', 'Kelas', 'Hadir', 'Izin', 'Sakit', 'Alpa', 'Kehadiran'],
            'widths' => ['5%', '12%', '23%', '14%', '9%', '9%', '9%', '9%', '10%'],
            'align' => ['center', 'left', 'left', 'left', 'center', 'center', 'center', 'center', 'center'],
            'rows' => $rows,
        ];
    }

    /**
     * @return array{title:string, heading:string, period:string, meta:list<array{0:string,1:string}>, columns:list<string>, widths:list<string>, align:list<string>, rows:list<list<string>>}
     */
    public function teacherAttendanceHistory(Teacher $teacher, Request $request): array
    {
        $sessions = AttendanceSession::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->withCount([
                'records as hadir_count' => fn ($query) => $query->where('status', AttendanceStatus::Present),
                'records as izin_count' => fn ($query) => $query->where('status', AttendanceStatus::Permitted),
                'records as sakit_count' => fn ($query) => $query->where('status', AttendanceStatus::Sick),
                'records as alpa_count' => fn ($query) => $query->where('status', AttendanceStatus::Absent),
            ])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')))
            ->orderBy('date')
            ->orderBy('id')
            ->get();

        $rows = $sessions->values()->map(function (AttendanceSession $session, int $index) {
            return [
                (string) ($index + 1),
                $this->longDate($session->date),
                $session->session_label,
                $session->schoolClass->name,
                $session->subject->name,
                (string) $session->hadir_count,
                (string) $session->izin_count,
                (string) $session->sakit_count,
                (string) $session->alpa_count,
            ];
        })->all();

        return [
            'title' => 'Riwayat Absensi '.$teacher->name,
            'heading' => 'RIWAYAT ABSENSI GURU',
            'period' => $this->period($request),
            'meta' => [
                ['Guru', $teacher->name],
                ['Periode Tanggal', $this->period($request)],
                ['Tanggal Cetak', $this->longDate(now())],
                ['Total Sesi', count($rows).' Sesi'],
            ],
            'columns' => ['No', 'Tanggal', 'Sesi', 'Kelas', 'Mata Pelajaran', 'Hadir', 'Izin', 'Sakit', 'Alpa'],
            'widths' => ['6%', '16%', '10%', '14%', '18%', '9%', '9%', '9%', '9%'],
            'align' => ['center', 'left', 'left', 'left', 'left', 'center', 'center', 'center', 'center'],
            'rows' => $rows,
        ];
    }

    private function classLabel(Student $student, int $classId): string
    {
        $names = $student->enrollments
            ->filter(fn ($enrollment) => ! $classId || (int) $enrollment->class_id === $classId)
            ->map(fn ($enrollment) => $enrollment->schoolClass?->name)
            ->filter()
            ->unique()
            ->values();

        return $names->isEmpty() ? '-' : $names->implode(', ');
    }

    private function attendancePercent(int $present, int $total): string
    {
        if ($total === 0) {
            return '0%';
        }

        $formatted = number_format($present / $total * 100, 1, ',', '.');
        if (str_ends_with($formatted, ',0')) {
            $formatted = substr($formatted, 0, -2);
        }

        return $formatted.'%';
    }

    private function teacherLabel(User $user, Request $request): string
    {
        if ($user->isTeacher()) {
            return $user->teacher?->name ?: $user->name;
        }

        if ($request->filled('teacher_id')) {
            $teacher = Teacher::query()->find($request->integer('teacher_id'));
            if ($teacher) {
                return $teacher->name;
            }
        }

        return 'Semua Guru';
    }

    private function longDate(Carbon|\DateTimeInterface|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return str_replace(' ', "\u{00A0}", Carbon::parse($value)->locale('id')->translatedFormat('d F Y'));
    }

    private function period(Request $request): string
    {
        $from = $request->filled('date_from') ? $this->longDate($request->date('date_from')) : null;
        $to = $request->filled('date_to') ? $this->longDate($request->date('date_to')) : null;
        if ($from && $to) {
            return $from.' s/d '.$to;
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
