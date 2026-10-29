<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Services\TeachingAuthorizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AttendanceSession::class);

        $sessions = AttendanceSession::query()
            ->visibleTo($request->user())
            ->with(['schoolClass', 'subject', 'teacher'])
            ->withCount('records')
            ->when($request->filled('date'), fn ($query) => $query->whereDate('date', $request->date('date')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        return view('attendance.index', [
            'sessions' => $sessions,
            'assignments' => app(TeachingAuthorizer::class)->assignments($request->user())->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AttendanceSession::class);
        $assignment = $this->selectedAssignment($request);
        $students = $assignment ? $assignment->schoolClass->activeStudents() : collect();

        return view('attendance.form', [
            'session' => new AttendanceSession([
                'date' => $request->date('date') ?? today(),
                'session_label' => $request->string('session_label')->toString() ?: 'Sesi 1',
            ]),
            'assignments' => app(TeachingAuthorizer::class)->assignments($request->user())->get(),
            'assignment' => $assignment,
            'students' => $students,
            'existing' => collect(),
            'statuses' => AttendanceStatus::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AttendanceSession::class);
        $session = $this->persist($request);

        return redirect()->route('attendance.show', $session)->with('success', 'Absensi tersimpan.');
    }

    public function show(AttendanceSession $attendance): View
    {
        $this->authorize('view', $attendance);
        $attendance->load(['records.student', 'schoolClass', 'subject', 'teacher', 'semester']);

        return view('attendance.show', [
            'session' => $attendance,
            'summary' => $this->summary($attendance),
        ]);
    }

    public function update(Request $request, AttendanceSession $attendance): RedirectResponse
    {
        $this->authorize('update', $attendance);
        $this->persist($request, $attendance);

        return redirect()->route('attendance.show', $attendance)->with('success', 'Absensi diperbarui.');
    }

    public function destroy(AttendanceSession $attendance): RedirectResponse
    {
        $this->authorize('delete', $attendance);

        DB::transaction(function () use ($attendance) {
            $attendance->records()->delete();
            $attendance->delete();
        });

        return redirect()->route('attendance.index')->with('success', 'Sesi absensi dihapus.');
    }

    public function recap(Request $request): View
    {
        $this->authorize('viewAny', AttendanceSession::class);
        $user = $request->user();
        $classId = $request->integer('class_id');

        if ($classId && $user->isTeacher()) {
            $allowed = $user->teacher?->assignments()->pluck('class_id') ?? collect();
            abort_unless($allowed->contains($classId), 403);
        }

        $rows = collect();
        if ($classId) {
            $students = Student::query()
                ->where('status', 'aktif')
                ->whereHas('enrollments', fn ($query) => $query->where('class_id', $classId)->where('status', EnrollmentStatus::Active))
                ->orderBy('name')
                ->get();

            $records = AttendanceRecord::query()
                ->whereIn('student_id', $students->pluck('id'))
                ->whereHas('session', function ($query) use ($request, $classId, $user) {
                    $query->visibleTo($user)->where('class_id', $classId);
                    if ($request->filled('date_from')) {
                        $query->whereDate('date', '>=', $request->date('date_from'));
                    }
                    if ($request->filled('date_to')) {
                        $query->whereDate('date', '<=', $request->date('date_to'));
                    }
                    if ($request->filled('subject_id')) {
                        $query->where('subject_id', $request->integer('subject_id'));
                    }
                })
                ->get()
                ->groupBy('student_id');

            $rows = $students->map(function (Student $student) use ($records) {
                $items = $records->get($student->id, collect());

                return [
                    'student' => $student,
                    'hadir' => $items->where('status', AttendanceStatus::Present)->count(),
                    'izin' => $items->where('status', AttendanceStatus::Permitted)->count(),
                    'sakit' => $items->where('status', AttendanceStatus::Sick)->count(),
                    'alpa' => $items->where('status', AttendanceStatus::Absent)->count(),
                ];
            });
        }

        $classes = SchoolClass::query()->orderBy('name');
        if ($user->isTeacher()) {
            $classes->whereIn('id', $user->teacher?->assignments()->pluck('class_id') ?? []);
        }

        return view('attendance.recap', [
            'rows' => $rows,
            'classes' => $classes->get(),
            'assignments' => app(TeachingAuthorizer::class)->assignments($user)->get(),
        ]);
    }

    private function persist(Request $request, ?AttendanceSession $session = null): AttendanceSession
    {
        $data = $request->validate([
            'assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'date' => ['required', 'date'],
            'session_label' => ['required', 'string', 'max:50'],
            'journal_id' => ['nullable', 'exists:teaching_journals,id'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:hadir,izin,sakit,alpa'],
            'note' => ['nullable', 'array'],
            'note.*' => ['nullable', 'string', 'max:300'],
        ]);

        $assignment = TeachingAssignment::query()->with('schoolClass')->findOrFail($data['assignment_id']);
        $user = $request->user();
        if ($user->isTeacher() && $user->teacher?->id !== $assignment->teacher_id) {
            abort(403);
        }

        $duplicate = AttendanceSession::query()
            ->where('class_id', $assignment->class_id)
            ->where('subject_id', $assignment->subject_id)
            ->whereDate('date', $data['date'])
            ->where('session_label', $data['session_label'])
            ->when($session, fn ($query) => $query->whereKeyNot($session->id))
            ->exists();

        if ($duplicate) {
            abort(redirect()->back()->withInput()->with('error', 'Absensi untuk kelas, mata pelajaran, tanggal, dan sesi ini sudah ada.'));
        }

        $students = $assignment->schoolClass->activeStudents();
        $missing = $students->pluck('id')->map(fn ($id) => (string) $id)->diff(collect(array_keys($data['status']))->map(fn ($id) => (string) $id));
        if ($missing->isNotEmpty()) {
            abort(redirect()->back()->withInput()->with('error', 'Status setiap siswa wajib diisi.'));
        }

        return DB::transaction(function () use ($assignment, $data, $students, $session) {
            $payload = [
                'teacher_id' => $assignment->teacher_id,
                'class_id' => $assignment->class_id,
                'subject_id' => $assignment->subject_id,
                'academic_year_id' => $assignment->academic_year_id,
                'semester_id' => $assignment->semester_id,
                'journal_id' => ($data['journal_id'] ?? null) ?: null,
                'date' => $data['date'],
                'session_label' => $data['session_label'],
            ];

            $session = $session ? tap($session)->update($payload) : AttendanceSession::query()->create($payload);

            foreach ($students as $student) {
                AttendanceRecord::query()->updateOrCreate(
                    ['attendance_session_id' => $session->id, 'student_id' => $student->id],
                    [
                        'status' => $data['status'][$student->id] ?? $data['status'][(string) $student->id],
                        'note' => ($data['note'] ?? [])[$student->id] ?? ($data['note'] ?? [])[(string) $student->id] ?? null,
                    ],
                );
            }

            return $session;
        });
    }

    private function selectedAssignment(Request $request): ?TeachingAssignment
    {
        if (! $request->filled('assignment_id')) {
            return null;
        }

        $assignment = TeachingAssignment::query()->with('schoolClass')->find($request->integer('assignment_id'));
        if (! $assignment) {
            return null;
        }

        if ($request->user()->isTeacher() && $request->user()->teacher?->id !== $assignment->teacher_id) {
            abort(403);
        }

        return $assignment;
    }

    /**
     * @return array<string, int>
     */
    private function summary(AttendanceSession $session): array
    {
        $counts = $session->records->countBy(fn ($record) => $record->status->value);

        return [
            'hadir' => (int) ($counts[AttendanceStatus::Present->value] ?? 0),
            'izin' => (int) ($counts[AttendanceStatus::Permitted->value] ?? 0),
            'sakit' => (int) ($counts[AttendanceStatus::Sick->value] ?? 0),
            'alpa' => (int) ($counts[AttendanceStatus::Absent->value] ?? 0),
        ];
    }
}
