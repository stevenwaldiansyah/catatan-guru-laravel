<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Models\User;
use App\Services\ScheduleConflictChecker;
use App\Support\Days;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Schedule::class);

        if ($request->user()->isAdmin() && ! $request->filled('guru')) {
            return view('schedules.index', [
                'selectedTeacher' => null,
                'teachers' => Teacher::query()->orderBy('name')->withCount('assignments')->get(),
                'scheduleCounts' => Schedule::query()
                    ->join('teaching_assignments', 'teaching_assignments.id', '=', 'schedules.teaching_assignment_id')
                    ->selectRaw('teaching_assignments.teacher_id as teacher_id, count(*) as total')
                    ->groupBy('teaching_assignments.teacher_id')
                    ->pluck('total', 'teacher_id'),
                'days' => Days::options(),
            ]);
        }

        $selectedTeacher = $request->user()->isAdmin()
            ? Teacher::query()->find($request->integer('guru'))
            : $request->user()->teacher;

        if ($request->user()->isAdmin() && ! $selectedTeacher) {
            return redirect()->route('schedules.index')->with('error', 'Guru tidak ditemukan.');
        }

        $assignments = $this->assignmentsFor($request->user()->isAdmin() ? null : $request->user())
            ->when($selectedTeacher, fn ($items) => $items->where('teacher_id', $selectedTeacher->id))
            ->values();

        $week = Schedule::query()
            ->visibleTo($request->user())
            ->when($selectedTeacher, fn ($query) => $query->whereHas(
                'assignment',
                fn ($assignment) => $assignment->where('teacher_id', $selectedTeacher->id),
            ))
            ->with(['assignment.schoolClass', 'assignment.subject', 'assignment.semester'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        return view('schedules.index', [
            'selectedTeacher' => $selectedTeacher,
            'assignments' => $assignments,
            'week' => $week,
            'days' => Days::options(),
            'classes' => $request->user()->isAdmin() ? SchoolClass::query()->with('academicYear')->orderBy('name')->get() : collect(),
            'subjects' => $request->user()->isAdmin() ? Subject::query()->where('is_active', true)->orderBy('name')->get() : collect(),
            'semesters' => $request->user()->isAdmin() ? Semester::query()->with('academicYear')->orderByDesc('id')->get() : collect(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', Schedule::class);

        $teacher = Teacher::query()->find($request->integer('guru'));
        if (! $teacher) {
            return redirect()->route('schedules.index')->with('error', 'Pilih guru terlebih dahulu.');
        }

        return view('schedules.form', $this->formData(new Schedule, $teacher));
    }

    public function store(Request $request, ScheduleConflictChecker $conflicts): RedirectResponse
    {
        $this->authorize('create', Schedule::class);
        $data = $this->validated($request);
        $messages = $conflicts->messages($data['teaching_assignment_id'], $data['day_of_week'], $data['start_time'], $data['end_time'], $data['room'] ?? null);

        if ($messages) {
            return back()->withInput()->withErrors(['start_time' => $messages[0]]);
        }

        Schedule::query()->create($data);

        return $this->backToTeacher($data['teaching_assignment_id'])->with('success', 'Jadwal ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        $schedule->load('assignment.teacher');

        return view('schedules.form', $this->formData($schedule, $schedule->assignment?->teacher));
    }

    public function update(Request $request, Schedule $schedule, ScheduleConflictChecker $conflicts): RedirectResponse
    {
        $this->authorize('update', $schedule);
        $data = $this->validated($request);
        $messages = $conflicts->messages($data['teaching_assignment_id'], $data['day_of_week'], $data['start_time'], $data['end_time'], $data['room'] ?? null, $schedule->id);

        if ($messages) {
            return back()->withInput()->withErrors(['start_time' => $messages[0]]);
        }

        $schedule->update($data);

        return $this->backToTeacher($data['teaching_assignment_id'])->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);
        $teacherId = $schedule->assignment?->teacher_id;
        $schedule->delete();

        return redirect()
            ->route('schedules.index', $teacherId ? ['guru' => $teacherId] : [])
            ->with('success', 'Jadwal dihapus. Jurnal yang terkait tidak ikut terhapus.');
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
        ]);

        $class = SchoolClass::query()->findOrFail($data['class_id']);
        $semester = Semester::query()->findOrFail($data['semester_id']);

        if ((int) $class->academic_year_id !== (int) $semester->academic_year_id) {
            return back()->withInput()->with('error', 'Kelas dan semester tidak berada pada tahun ajaran yang sama.');
        }

        $exists = TeachingAssignment::query()->where($data)->exists();
        if ($exists) {
            return back()->with('error', 'Penugasan yang sama sudah ada.');
        }

        TeachingAssignment::query()->create($data + ['academic_year_id' => $class->academic_year_id]);

        return back()->with('success', 'Penugasan mengajar disimpan.');
    }

    public function destroyAssignment(Request $request, TeachingAssignment $assignment): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        if ($assignment->schedules()->exists()) {
            return back()->with('error', 'Hapus jadwal pada penugasan ini terlebih dahulu.');
        }

        $used = TeachingJournal::query()
            ->where('teacher_id', $assignment->teacher_id)
            ->where('class_id', $assignment->class_id)
            ->where('subject_id', $assignment->subject_id)
            ->where('semester_id', $assignment->semester_id)
            ->exists();

        if ($used) {
            return back()->with('error', 'Penugasan masih memiliki jurnal sehingga tidak dapat dihapus.');
        }

        $assignment->delete();

        return back()->with('success', 'Penugasan dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Schedule $schedule, ?Teacher $teacher = null): array
    {
        $teacher ??= $schedule->assignment?->teacher;

        return [
            'schedule' => $schedule,
            'teacher' => $teacher,
            'assignments' => $this->assignmentsFor()
                ->when($teacher, fn ($items) => $items->where('teacher_id', $teacher->id))
                ->values(),
            'days' => Days::options(),
        ];
    }

    private function backToTeacher(int $assignmentId): RedirectResponse
    {
        $teacherId = TeachingAssignment::query()->whereKey($assignmentId)->value('teacher_id');

        return redirect()->route('schedules.index', $teacherId ? ['guru' => $teacherId] : []);
    }

    private function assignmentsFor(?User $user = null)
    {
        return TeachingAssignment::query()
            ->when($user?->isTeacher(), fn ($query) => $query->where('teacher_id', $user->teacher?->id ?? 0))
            ->with(['teacher', 'schoolClass', 'subject', 'semester'])
            ->get()
            ->sortBy(fn (TeachingAssignment $assignment) => mb_strtolower(implode(' ', [
                $assignment->teacher?->name,
                $assignment->schoolClass?->name,
                $assignment->subject?->name,
                $assignment->semester?->name,
            ])), SORT_STRING)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'teaching_assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'day_of_week' => ['required', 'integer', Rule::in(array_keys(Days::options()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
            'label' => ['nullable', 'string', 'max:50'],
        ]);

        $data['start_time'] = $data['start_time'].':00';
        $data['end_time'] = $data['end_time'].':00';
        $data['room'] = $data['room'] ?: null;
        $data['label'] = $data['label'] ?: null;

        return $data;
    }
}
