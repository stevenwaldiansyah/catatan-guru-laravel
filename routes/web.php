<?php

use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolSettingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentNoteController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeachingJournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-sandi', [ProfileController::class, 'password'])->name('profile.password');

    Route::middleware('role:admin')->group(function () {
        Route::resource('guru', TeacherController::class)->parameters(['guru' => 'teacher'])->names('teachers')->except(['show']);
        Route::resource('kelas', SchoolClassController::class)->parameters(['kelas' => 'schoolClass'])->names('classes');
        Route::resource('mata-pelajaran', SubjectController::class)->parameters(['mata-pelajaran' => 'subject'])->names('subjects')->except(['show']);
        Route::resource('tahun-ajaran', AcademicYearController::class)->parameters(['tahun-ajaran' => 'academicYear'])->names('academic-years')->except(['show']);
        Route::get('/pengaturan', [SchoolSettingController::class, 'edit'])->name('settings.edit');
        Route::put('/pengaturan', [SchoolSettingController::class, 'update'])->name('settings.update');
        Route::post('/penugasan', [ScheduleController::class, 'storeAssignment'])->name('assignments.store');
        Route::delete('/penugasan/{assignment}', [ScheduleController::class, 'destroyAssignment'])->name('assignments.destroy');
    });

    Route::resource('siswa', StudentController::class)->parameters(['siswa' => 'student'])->names('students');
    Route::resource('jadwal', ScheduleController::class)->parameters(['jadwal' => 'schedule'])->names('schedules')->except(['show']);
    Route::resource('jurnal', TeachingJournalController::class)->parameters(['jurnal' => 'journal'])->names('journals');

    Route::get('/absensi/rekap', [AttendanceController::class, 'recap'])->name('attendance.recap');
    Route::resource('absensi', AttendanceController::class)->parameters(['absensi' => 'attendance'])->names('attendance')->except(['edit']);

    Route::resource('catatan', StudentNoteController::class)->parameters(['catatan' => 'note'])->names('notes');

    Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/laporan/{type}/csv', [ReportController::class, 'csv'])->name('reports.csv');
    Route::get('/laporan/{type}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');
    Route::get('/laporan/{type}', [ReportController::class, 'show'])->name('reports.show');
});
