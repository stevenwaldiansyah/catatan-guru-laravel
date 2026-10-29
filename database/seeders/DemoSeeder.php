<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\JournalStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\ClassEnrollment;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        DB::transaction(function () {
            $year = AcademicYear::query()->create([
                'name' => '2026/2027',
                'starts_on' => '2026-07-13',
                'ends_on' => '2027-06-18',
                'is_active' => true,
            ]);

            $ganjil = Semester::query()->create([
                'academic_year_id' => $year->id,
                'name' => 'Ganjil',
                'number' => 1,
                'starts_on' => '2026-07-13',
                'ends_on' => '2026-12-18',
                'is_active' => true,
            ]);

            Semester::query()->create([
                'academic_year_id' => $year->id,
                'name' => 'Genap',
                'number' => 2,
                'starts_on' => '2027-01-04',
                'ends_on' => '2027-06-18',
                'is_active' => false,
            ]);

            SchoolSetting::query()->create([
                'school_name' => 'SMK Negeri 1 Contoh',
                'npsn' => '12345678',
                'address' => 'Jl. Pendidikan No. 1',
                'phone' => '021000000',
                'email' => 'admin@smkn1contoh.sch.id',
                'active_academic_year_id' => $year->id,
                'active_semester_id' => $ganjil->id,
                'prevent_teacher_overlap' => true,
                'prevent_class_overlap' => true,
                'prevent_room_overlap' => false,
            ]);

            User::query()->create([
                'name' => 'Administrator',
                'username' => 'admin',
                'email' => 'admin@edujurnal.local',
                'password' => 'Admin#2026',
                'role' => UserRole::Admin,
                'is_active' => true,
            ]);

            $sitiUser = User::query()->create([
                'name' => 'Siti Rahma',
                'username' => 'siti',
                'email' => 'siti@edujurnal.local',
                'password' => 'Guru#2026',
                'role' => UserRole::Teacher,
                'is_active' => true,
            ]);

            $budiUser = User::query()->create([
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'email' => 'budi@edujurnal.local',
                'password' => 'Guru#2026',
                'role' => UserRole::Teacher,
                'is_active' => true,
            ]);

            $siti = Teacher::query()->create([
                'user_id' => $sitiUser->id,
                'nip' => '198501152010012001',
                'name' => 'Siti Rahma',
                'phone' => '081200000001',
            ]);

            $budi = Teacher::query()->create([
                'user_id' => $budiUser->id,
                'nip' => '198703202012011002',
                'name' => 'Budi Santoso',
                'phone' => '081200000002',
            ]);

            $classX = SchoolClass::query()->create([
                'academic_year_id' => $year->id,
                'name' => 'X RPL 1',
                'level' => 'X',
                'major' => 'RPL',
                'homeroom_teacher_id' => $siti->id,
            ]);

            $classXi = SchoolClass::query()->create([
                'academic_year_id' => $year->id,
                'name' => 'XI RPL 1',
                'level' => 'XI',
                'major' => 'RPL',
                'homeroom_teacher_id' => $budi->id,
            ]);

            $math = Subject::query()->create(['code' => 'MTK', 'name' => 'Matematika', 'is_active' => true]);
            $indonesian = Subject::query()->create(['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'is_active' => true]);

            $mathX = TeachingAssignment::query()->create([
                'teacher_id' => $siti->id,
                'class_id' => $classX->id,
                'subject_id' => $math->id,
                'academic_year_id' => $year->id,
                'semester_id' => $ganjil->id,
            ]);

            TeachingAssignment::query()->create([
                'teacher_id' => $siti->id,
                'class_id' => $classXi->id,
                'subject_id' => $math->id,
                'academic_year_id' => $year->id,
                'semester_id' => $ganjil->id,
            ]);

            TeachingAssignment::query()->create([
                'teacher_id' => $budi->id,
                'class_id' => $classX->id,
                'subject_id' => $indonesian->id,
                'academic_year_id' => $year->id,
                'semester_id' => $ganjil->id,
            ]);

            foreach (range(1, 5) as $day) {
                $mathX->schedules()->create([
                    'day_of_week' => $day,
                    'start_time' => '07:00:00',
                    'end_time' => '08:30:00',
                    'room' => 'R-1',
                    'label' => 'Jam 1',
                ]);
            }

            $students = [
                ['nis' => '2627001', 'name' => 'Andi Pratama', 'gender' => 'L'],
                ['nis' => '2627002', 'name' => 'Bunga Lestari', 'gender' => 'P'],
                ['nis' => '2627003', 'name' => 'Citra Dewi', 'gender' => 'P'],
                ['nis' => '2627004', 'name' => 'Dimas Arya', 'gender' => 'L'],
            ];

            $models = [];
            foreach ($students as $row) {
                $student = Student::query()->create([
                    'nis' => $row['nis'],
                    'name' => $row['name'],
                    'gender' => $row['gender'],
                    'status' => 'aktif',
                ]);
                ClassEnrollment::query()->create([
                    'student_id' => $student->id,
                    'class_id' => $classX->id,
                    'academic_year_id' => $year->id,
                    'status' => EnrollmentStatus::Active,
                    'started_on' => '2026-07-13',
                ]);
                $models[] = $student;
            }

            $journal = TeachingJournal::query()->create([
                'teacher_id' => $siti->id,
                'class_id' => $classX->id,
                'subject_id' => $math->id,
                'academic_year_id' => $year->id,
                'semester_id' => $ganjil->id,
                'date' => now()->toDateString(),
                'session_label' => 'Jam 1',
                'material' => 'Persamaan linear satu variabel',
                'activities' => 'Diskusi contoh soal dan latihan terarah.',
                'method' => 'Diskusi',
                'notes' => null,
                'status' => JournalStatus::Completed,
                'completed_at' => now(),
            ]);

            $session = AttendanceSession::query()->create([
                'teacher_id' => $siti->id,
                'class_id' => $classX->id,
                'subject_id' => $math->id,
                'academic_year_id' => $year->id,
                'semester_id' => $ganjil->id,
                'journal_id' => $journal->id,
                'date' => now()->toDateString(),
                'session_label' => 'Jam 1',
            ]);

            $statuses = [AttendanceStatus::Present, AttendanceStatus::Permitted, AttendanceStatus::Sick, AttendanceStatus::Absent];
            foreach ($models as $index => $student) {
                AttendanceRecord::query()->create([
                    'attendance_session_id' => $session->id,
                    'student_id' => $student->id,
                    'status' => $statuses[$index],
                ]);
            }

            StudentNote::query()->create([
                'teacher_id' => $siti->id,
                'student_id' => $models[0]->id,
                'class_id' => $classX->id,
                'date' => now()->toDateString(),
                'category' => 'akademik',
                'description' => 'Andi mulai lebih teliti pada langkah pengerjaan, tetapi masih terburu-buru.',
                'follow_up' => 'Minta Andi menjelaskan jawabannya secara lisan pada pertemuan berikutnya.',
            ]);
        });
    }
}
