<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Support\NoteCategories;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentNoteController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StudentNote::class);

        $notes = StudentNote::query()
            ->visibleTo($request->user())
            ->with(['student', 'schoolClass', 'teacher'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(function ($query) use ($term) {
                    Search::where($query, 'description', $term);
                    $query->orWhereHas('student', function ($student) use ($term) {
                        Search::where($student, 'name', $term);
                    });
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->latest('date')
            ->paginate(10)
            ->withQueryString();

        return view('notes.index', [
            'notes' => $notes,
            'categories' => NoteCategories::options(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StudentNote::class);

        return view('notes.form', [
            'note' => new StudentNote(['date' => today(), 'category' => 'akademik']),
            'students' => Student::query()->visibleTo($request->user())->orderBy('name')->get(),
            'classes' => $this->classes($request),
            'categories' => NoteCategories::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StudentNote::class);
        $note = StudentNote::query()->create($this->payload($request));

        return redirect()->route('notes.show', $note)->with('success', 'Catatan siswa tersimpan.');
    }

    public function show(StudentNote $note): View
    {
        $this->authorize('view', $note);
        $note->load(['student', 'schoolClass', 'teacher']);

        return view('notes.show', compact('note'));
    }

    public function edit(Request $request, StudentNote $note): View
    {
        $this->authorize('update', $note);

        return view('notes.form', [
            'note' => $note,
            'students' => Student::query()->visibleTo($request->user())->orderBy('name')->get(),
            'classes' => $this->classes($request),
            'categories' => NoteCategories::options(),
        ]);
    }

    public function update(Request $request, StudentNote $note): RedirectResponse
    {
        $this->authorize('update', $note);
        $note->update($this->payload($request, $note));

        return redirect()->route('notes.show', $note)->with('success', 'Catatan diperbarui.');
    }

    public function destroy(StudentNote $note): RedirectResponse
    {
        $this->authorize('delete', $note);
        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Catatan diarsipkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Request $request, ?StudentNote $note = null): array
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'date' => ['required', 'date'],
            'category' => ['required', Rule::in(array_keys(NoteCategories::options()))],
            'description' => ['required', 'string', 'max:5000'],
            'follow_up' => ['nullable', 'string', 'max:5000'],
        ]);

        if ($note) {
            $data['student_id'] = $note->student_id;
            $data['class_id'] = $note->class_id;
        }

        $user = $request->user();
        $student = Student::query()->visibleTo($user)->find($data['student_id']);
        abort_unless($student, 403);

        $teachesClass = $user->isAdmin() || $user->teacher?->assignments()->where('class_id', $data['class_id'])->exists();
        abort_unless($teachesClass, 403);

        $enrolled = $student->enrollments()
            ->where('class_id', $data['class_id'])
            ->where('status', EnrollmentStatus::Active)
            ->exists();
        abort_unless($enrolled, 422, 'Siswa tidak terdaftar aktif di kelas tersebut.');

        $teacherId = $note?->teacher_id ?? $user->teacher?->id;
        abort_unless($teacherId, 403, 'Catatan perkembangan dicatat melalui akun guru.');

        return [
            'teacher_id' => $teacherId,
            'student_id' => $note?->student_id ?? $student->id,
            'class_id' => $note?->class_id ?? $data['class_id'],
            'date' => $data['date'],
            'category' => $data['category'],
            'description' => $data['description'],
            'follow_up' => $data['follow_up'] ?? null,
        ];
    }

    private function classes(Request $request)
    {
        $query = SchoolClass::query()->orderBy('name');
        if ($request->user()->isTeacher()) {
            $query->whereIn('id', $request->user()->teacher?->assignments()->pluck('class_id') ?? []);
        }

        return $query->get();
    }
}
