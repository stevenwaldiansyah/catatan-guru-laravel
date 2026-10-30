<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Services\ScheduleConflictChecker;
use App\Support\Days;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Schedule::class);

        $schedules = Schedule::query()
            ->visibleTo($request->user())
            ->with(['assignment.teacher', 'assignment.schoolClass', 'assignment.subject', 'assignment.semester'])
            ->when($request->filled('day'), fn ($query) => $query->where('day_of_week', $request->integer('day')))
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        $assignments = TeachingAssignment::query()
            ->when($request->user()->isTeacher(), fn ($query) => $query->where('teacher_id', $request->user()->teacher?->id ?? 0))
            ->with(['teacher', 'schoolClass', 'subject', 'semester'])
            ->orderBy('teacher_id')
            ->get();

        return view('schedules.index', [
            'schedules' => $schedules,
            'assignments' => $assignments,
            'days' => Days::options(),
            'teachers' => Teacher::query()->orderBy('name')->get(),
            'classes' => SchoolClass::query()->with('academicYear')->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'semesters' => Semester::query()->with('academicYear')->orderByDesc('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Schedule::class);

        return view('schedules.form', $this->formData(new Schedule));
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

        return redirect()->route('schedules.index')->with('success', 'Jadwal ditambahkan.');
    }

    public function edit(Schedule $schedule): View
    {
        $this->authorize('update', $schedule);

        return view('schedules.form', $this->formData($schedule));
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

        return redirect()->route('schedules.index')->with('success', 'Jadwal diperbarui.');
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        $this->authorize('delete', $schedule);
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Jadwal dihapus. Jurnal yang terkait tidak ikut terhapus.');
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
    private function formData(Schedule $schedule): array
    {
        return [
            'schedule' => $schedule,
            'assignments' => TeachingAssignment::query()->with(['teacher', 'schoolClass', 'subject', 'semester'])->get(),
            'days' => Days::options(),
        ];
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
