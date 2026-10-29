<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\SchoolSetting;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolSettingController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'settings' => SchoolSetting::query()->first() ?? new SchoolSetting([
                'school_name' => 'EduJurnal',
                'prevent_teacher_overlap' => true,
                'prevent_class_overlap' => true,
            ]),
            'years' => AcademicYear::query()->orderByDesc('name')->get(),
            'semesters' => Semester::query()->with('academicYear')->orderByDesc('academic_year_id')->orderBy('number')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'active_academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'active_semester_id' => ['nullable', 'exists:semesters,id'],
        ]);

        $semester = isset($data['active_semester_id']) ? Semester::query()->find($data['active_semester_id']) : null;
        if ($semester && (int) $data['active_academic_year_id'] !== (int) $semester->academic_year_id) {
            return back()->withInput()->with('error', 'Semester aktif harus berada pada tahun ajaran yang dipilih.');
        }

        DB::transaction(function () use ($request, $data) {
            AcademicYear::query()->update(['is_active' => false]);
            Semester::query()->update(['is_active' => false]);

            if (! empty($data['active_academic_year_id'])) {
                AcademicYear::query()->whereKey($data['active_academic_year_id'])->update(['is_active' => true]);
            }

            if (! empty($data['active_semester_id'])) {
                Semester::query()->whereKey($data['active_semester_id'])->update(['is_active' => true]);
            }

            $settings = SchoolSetting::query()->first() ?? new SchoolSetting;
            $settings->fill($data);
            $settings->prevent_teacher_overlap = $request->boolean('prevent_teacher_overlap');
            $settings->prevent_class_overlap = $request->boolean('prevent_class_overlap');
            $settings->prevent_room_overlap = $request->boolean('prevent_room_overlap');
            $settings->save();
        });

        return back()->with('success', 'Pengaturan sekolah disimpan.');
    }
}
