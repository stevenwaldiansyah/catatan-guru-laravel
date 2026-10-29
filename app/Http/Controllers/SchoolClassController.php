<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolClassController extends Controller
{
    public function index(): View
    {
        $classes = SchoolClass::query()
            ->with(['academicYear', 'homeroomTeacher'])
            ->withCount(['enrollments as active_students_count' => fn ($query) => $query->where('status', EnrollmentStatus::Active)])
            ->orderByDesc('academic_year_id')
            ->orderBy('name')
            ->paginate(10);

        return view('classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('classes.form', [
            'class' => new SchoolClass,
            'years' => AcademicYear::query()->orderByDesc('name')->get(),
            'teachers' => Teacher::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        SchoolClass::query()->create($data);

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil dibuat.');
    }

    public function show(SchoolClass $schoolClass): View
    {
        $schoolClass->load(['academicYear', 'homeroomTeacher']);
        $students = $schoolClass->activeStudents();

        return view('classes.show', ['class' => $schoolClass, 'students' => $students]);
    }

    public function edit(SchoolClass $schoolClass): View
    {
        return view('classes.form', [
            'class' => $schoolClass,
            'years' => AcademicYear::query()->orderByDesc('name')->get(),
            'teachers' => Teacher::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SchoolClass $schoolClass): RedirectResponse
    {
        $schoolClass->update($this->validated($request, $schoolClass));

        return redirect()->route('classes.index')->with('success', 'Kelas diperbarui.');
    }

    public function destroy(SchoolClass $schoolClass): RedirectResponse
    {
        if ($schoolClass->enrollments()->exists() || $schoolClass->assignments()->exists() || $schoolClass->journals()->exists()) {
            return back()->with('error', 'Kelas masih memiliki siswa, penugasan, atau jurnal sehingga tidak dapat dihapus.');
        }

        $schoolClass->delete();

        return redirect()->route('classes.index')->with('success', 'Kelas dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?SchoolClass $class = null): array
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('classes', 'name')->where(fn ($query) => $query->where('academic_year_id', $request->integer('academic_year_id')))->ignore($class?->id),
            ],
            'level' => ['nullable', 'string', 'max:20'],
            'major' => ['nullable', 'string', 'max:100'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;

        return $data;
    }
}
