<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\TeachingAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $years = AcademicYear::query()->with('semesters')->orderByDesc('starts_on')->paginate(10);

        return view('academic-years.index', compact('years'));
    }

    public function create(): View
    {
        return view('academic-years.form', ['year' => new AcademicYear]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:academic_years,name'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ]);

        DB::transaction(function () use ($data) {
            $year = AcademicYear::query()->create($data + ['is_active' => false]);
            $start = $year->starts_on->copy();
            $mid = $year->starts_on->copy()->addMonths(6);

            $year->semesters()->createMany([
                [
                    'name' => 'Ganjil',
                    'number' => 1,
                    'starts_on' => $start->toDateString(),
                    'ends_on' => $mid->copy()->subDay()->toDateString(),
                    'is_active' => false,
                ],
                [
                    'name' => 'Genap',
                    'number' => 2,
                    'starts_on' => $mid->toDateString(),
                    'ends_on' => $year->ends_on->toDateString(),
                    'is_active' => false,
                ],
            ]);
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran dan dua semester dibuat. Atur yang aktif lewat Pengaturan.');
    }

    public function edit(AcademicYear $academicYear): View
    {
        $academicYear->load('semesters');

        return view('academic-years.form', ['year' => $academicYear]);
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:20', Rule::unique('academic_years', 'name')->ignore($academicYear->id)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'semesters' => ['required', 'array'],
            'semesters.*.id' => ['required', 'exists:semesters,id'],
            'semesters.*.starts_on' => ['required', 'date'],
            'semesters.*.ends_on' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($academicYear, $data) {
            $academicYear->update([
                'name' => $data['name'],
                'starts_on' => $data['starts_on'],
                'ends_on' => $data['ends_on'],
            ]);

            foreach ($data['semesters'] as $semester) {
                $academicYear->semesters()->whereKey($semester['id'])->update([
                    'starts_on' => $semester['starts_on'],
                    'ends_on' => $semester['ends_on'],
                ]);
            }
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tahun ajaran yang sedang aktif tidak dapat dihapus.');
        }

        if ($academicYear->classes()->exists() || TeachingAssignment::query()->where('academic_year_id', $academicYear->id)->exists()) {
            return back()->with('error', 'Tahun ajaran masih memiliki kelas atau penugasan.');
        }

        DB::transaction(function () use ($academicYear) {
            $academicYear->semesters()->delete();
            $academicYear->delete();
        });

        return redirect()->route('academic-years.index')->with('success', 'Tahun ajaran dihapus.');
    }
}
