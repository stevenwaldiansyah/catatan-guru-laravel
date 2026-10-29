<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        return $request->user()->isAdmin()
            ? view('dashboard.admin', $this->adminData())
            : view('dashboard.teacher', $this->teacherData($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function adminData(): array
    {
        $settings = \App\Models\SchoolSetting::query()->with(['activeYear', 'activeSemester'])->first();
        $yearId = $settings?->active_academic_year_id;

        $attendanceRows = AttendanceRecord::query()
            ->whereHas('session', fn ($query) => $query->whereDate('date', today()))
            ->get();
        $attendance = [
            AttendanceStatus::Present->value => $attendanceRows->where('status', AttendanceStatus::Present)->count(),
            AttendanceStatus::Permitted->value => $attendanceRows->where('status', AttendanceStatus::Permitted)->count(),
            AttendanceStatus::Sick->value => $attendanceRows->where('status', AttendanceStatus::Sick)->count(),
            AttendanceStatus::Absent->value => $attendanceRows->where('status', AttendanceStatus::Absent)->count(),
        ];

        $months = collect(range(5, 0))->map(function (int $ago) {
            $month = now()->startOfMonth()->subMonths($ago);

            return [
                'label' => $month->translatedFormat('M yy'),
                'total' => TeachingJournal::query()
                    ->whereBetween('date', [$month->toDateString(), $month->copy()->endOfMonth()->toDateString()])
                    ->count(),
            ];
        });

        return [
            'settings' => $settings,
            'teacherCount' => Teacher::query()->count(),
            'studentCount' => Student::query()->where('status', 'aktif')->count(),
            'classCount' => SchoolClass::query()->when($yearId, fn ($query) => $query->where('academic_year_id', $yearId))->count(),
            'subjectCount' => Subject::query()->where('is_active', true)->count(),
            'journalMonth' => TeachingJournal::query()->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count(),
            'attendanceToday' => [
                'hadir' => (int) ($attendance[AttendanceStatus::Present->value] ?? 0),
                'izin' => (int) ($attendance[AttendanceStatus::Permitted->value] ?? 0),
                'sakit' => (int) ($attendance[AttendanceStatus::Sick->value] ?? 0),
                'alpa' => (int) ($attendance[AttendanceStatus::Absent->value] ?? 0),
            ],
            'recentJournals' => TeachingJournal::query()->with(['teacher', 'schoolClass', 'subject'])->latest('date')->limit(6)->get(),
            'journalMonths' => $months,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function teacherData(Request $request): array
    {
        $user = $request->user();
        $teacher = $user->teacher;
        $settings = \App\Models\SchoolSetting::query()->with('activeSemester')->first();
        $semesterId = $settings?->active_semester_id;

        if (! $teacher) {
            return [
                'missingProfile' => true,
                'settings' => $settings,
                'schedules' => collect(),
                'classCount' => 0,
                'journals' => collect(),
                'noteCount' => 0,
            ];
        }

        $schedules = Schedule::query()
            ->visibleTo($user)
            ->with(['assignment.schoolClass', 'assignment.subject'])
            ->where('day_of_week', now()->dayOfWeekIso)
            ->when($semesterId, fn ($query) => $query->whereHas(
                'assignment',
                fn ($assignment) => $assignment->where('semester_id', $semesterId)
            ))
            ->orderBy('start_time')
            ->get();

        foreach ($schedules as $schedule) {
            $schedule->setAttribute('attendance_done', AttendanceSession::query()
                ->where('class_id', $schedule->assignment->class_id)
                ->where('subject_id', $schedule->assignment->subject_id)
                ->whereDate('date', today())
                ->where('session_label', $schedule->sessionLabel())
                ->exists());
        }

        return [
            'missingProfile' => false,
            'settings' => $settings,
            'schedules' => $schedules,
            'classCount' => $teacher->assignments()->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))->pluck('class_id')->unique()->count(),
            'journals' => TeachingJournal::query()->visibleTo($user)->with(['schoolClass', 'subject'])->latest('date')->limit(5)->get(),
            'noteCount' => StudentNote::query()->visibleTo($user)->whereBetween('date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])->count(),
        ];
    }
}
