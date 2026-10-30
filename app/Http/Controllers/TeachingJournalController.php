<?php

namespace App\Http\Controllers;

use App\Enums\JournalStatus;
use App\Models\Schedule;
use App\Models\Semester;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Services\TeachingAuthorizer;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeachingJournalController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeachingJournal::class);

        $journals = TeachingJournal::query()
            ->visibleTo($request->user())
            ->with(['teacher', 'schoolClass', 'subject'])
            ->when($request->filled('q'), function ($query) use ($request) {
                Search::where($query, 'material', $request->string('q')->toString());
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')))
            ->when($request->filled('class_id'), fn ($query) => $query->where('class_id', $request->integer('class_id')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')))
            ->when($request->user()->isAdmin() && $request->filled('teacher_id'), fn ($query) => $query->where('teacher_id', $request->integer('teacher_id')))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        return view('journals.index', [
            'journals' => $journals,
            'assignments' => app(TeachingAuthorizer::class)->assignments($request->user())->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TeachingJournal::class);

        return view('journals.form', [
            'journal' => new TeachingJournal([
                'date' => today(),
                'status' => JournalStatus::Draft,
                'session_label' => 'Sesi 1',
            ]),
            'assignments' => app(TeachingAuthorizer::class)->assignments($request->user())->get(),
            'schedules' => Schedule::query()->visibleTo($request->user())->with('assignment')->orderBy('day_of_week')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TeachingJournal::class);
        $journal = TeachingJournal::query()->create($this->payload($request));

        return redirect()->route('journals.show', $journal)->with('success', 'Jurnal tersimpan.');
    }

    public function show(TeachingJournal $journal): View
    {
        $this->authorize('view', $journal);
        $journal->load(['teacher', 'schoolClass', 'subject', 'semester', 'attendanceSession']);

        return view('journals.show', compact('journal'));
    }

    public function edit(Request $request, TeachingJournal $journal): View
    {
        $this->authorize('update', $journal);

        return view('journals.form', [
            'journal' => $journal,
            'assignments' => app(TeachingAuthorizer::class)->assignments($request->user())->get(),
            'schedules' => Schedule::query()->visibleTo($request->user())->with('assignment')->orderBy('day_of_week')->get(),
        ]);
    }

    public function update(Request $request, TeachingJournal $journal): RedirectResponse
    {
        $this->authorize('update', $journal);
        $journal->update($this->payload($request, $journal));

        return redirect()->route('journals.show', $journal)->with('success', 'Jurnal diperbarui.');
    }

    public function destroy(TeachingJournal $journal): RedirectResponse
    {
        $this->authorize('delete', $journal);

        if ($journal->attendanceSession) {
            return back()->with('error', 'Jurnal masih terhubung ke absensi. Hapus sesi absensi terlebih dahulu jika memang perlu.');
        }

        $journal->delete();

        return redirect()->route('journals.index')->with('success', 'Jurnal diarsipkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?TeachingJournal $journal = null): array
    {
        $data = $request->validate([
            'assignment_id' => ['required', 'exists:teaching_assignments,id'],
            'date' => ['required', 'date'],
            'session_label' => ['required', 'string', 'max:50'],
            'material' => ['required', 'string', 'max:5000'],
            'activities' => ['nullable', 'string', 'max:5000', 'required_if:status,completed'],
            'method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,completed'],
            'schedule_id' => ['nullable', 'exists:schedules,id'],
        ]);

        $assignment = TeachingAssignment::query()->findOrFail($data['assignment_id']);
        $user = $request->user();

        if ($user->isTeacher() && $user->teacher?->id !== $assignment->teacher_id) {
            abort(403);
        }

        $semester = Semester::query()->findOrFail($assignment->semester_id);
        if ((int) $assignment->academic_year_id !== (int) $semester->academic_year_id) {
            abort(422, 'Data penugasan tidak konsisten.');
        }

        $scheduleId = ($data['schedule_id'] ?? null) ?: null;
        if ($scheduleId && ! Schedule::query()->whereKey($scheduleId)->where('teaching_assignment_id', $assignment->id)->exists()) {
            abort(422, 'Jadwal tidak termasuk penugasan yang dipilih.');
        }

        $status = JournalStatus::from($data['status']);
        if ($user->isTeacher() && $journal?->status === JournalStatus::Completed) {
            $status = JournalStatus::Completed;
        }

        return [
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id,
            'academic_year_id' => $assignment->academic_year_id,
            'semester_id' => $assignment->semester_id,
            'schedule_id' => $scheduleId,
            'date' => $data['date'],
            'session_label' => $data['session_label'],
            'material' => $data['material'],
            'activities' => $data['activities'] ?? null,
            'method' => $data['method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $status,
            'completed_at' => $status === JournalStatus::Completed ? ($journal?->completed_at ?? now()) : null,
        ];
    }
}
