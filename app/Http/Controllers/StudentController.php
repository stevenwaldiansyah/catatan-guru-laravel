<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Services\EnrollmentService;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Student::class);
        $user = $request->user();

        if ($request->filled('class_id') && $user->isTeacher()) {
            $allowed = $user->teacher?->assignments()->pluck('class_id') ?? collect();
            abort_unless($allowed->contains($request->integer('class_id')), 403);
        }

        $students = Student::query()
            ->visibleTo($user)
            ->with(['enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)->with('schoolClass')])
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = Search::like($request->string('q')->toString());
                $query->where(function ($query) use ($like) {
                    $query->where('name', 'like', $like)->orWhere('nis', 'like', $like)->orWhere('nisn', 'like', $like);
                });
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->whereHas('enrollments', fn ($enrollment) => $enrollment
                    ->where('class_id', $request->integer('class_id'))
                    ->where('status', EnrollmentStatus::Active));
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('students.index', [
            'students' => $students,
            'classes' => $this->classOptions($request),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Student::class);

        return view('students.form', [
            'student' => new Student(['gender' => 'L', 'status' => 'aktif']),
            'classes' => SchoolClass::query()->with('academicYear')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, EnrollmentService $enrollments): RedirectResponse
    {
        $this->authorize('create', Student::class);
        $data = $this->validated($request);

        $student = Student::query()->create($data);
        $enrollments->place($student, SchoolClass::query()->findOrFail($data['class_id']));

        return redirect()->route('students.show', $student)->with('success', 'Siswa ditambahkan.');
    }

    public function show(Request $request, Student $student): View
    {
        $this->authorize('view', $student);
        $student->load(['enrollments.schoolClass', 'enrollments.academicYear']);
        $notes = StudentNote::query()->visibleTo($request->user())->where('student_id', $student->id)->latest('date')->limit(10)->get();

        return view('students.show', compact('student', 'notes'));
    }

    public function edit(Student $student): View
    {
        $this->authorize('update', $student);
        $student->load(['enrollments' => fn ($query) => $query->where('status', EnrollmentStatus::Active)]);

        return view('students.form', [
            'student' => $student,
            'classes' => SchoolClass::query()->with('academicYear')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Student $student, EnrollmentService $enrollments): RedirectResponse
    {
        $this->authorize('update', $student);
        $data = $this->validated($request, $student);
        $student->update($data);
        $enrollments->place($student, SchoolClass::query()->findOrFail($data['class_id']));

        return redirect()->route('students.show', $student)->with('success', 'Data siswa diperbarui.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->authorize('delete', $student);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Data siswa dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'nis' => ['nullable', 'string', 'max:20', 'required_without:nisn', Rule::unique('students', 'nis')->ignore($student?->id)],
            'nisn' => ['nullable', 'string', 'max:20', 'required_without:nis', Rule::unique('students', 'nisn')->ignore($student?->id)],
            'gender' => ['required', 'in:L,P'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'class_id' => ['required', 'exists:classes,id'],
        ]);
    }

    private function classOptions(Request $request)
    {
        $query = SchoolClass::query()->with('academicYear')->orderBy('name');

        if ($request->user()->isTeacher()) {
            $ids = $request->user()->teacher?->assignments()->pluck('class_id') ?? collect();
            $query->whereIn('id', $ids);
        }

        return $query->get();
    }
}
