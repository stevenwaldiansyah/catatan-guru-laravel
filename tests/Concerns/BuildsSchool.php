<?php

namespace Tests\Concerns;

use App\Enums\EnrollmentStatus;
use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;

trait BuildsSchool
{
    /**
     * @return array<string, mixed>
     */
    protected function buildSchool(): array
    {
        $year = AcademicYear::query()->create([
            'name' => '2026/2027',
            'starts_on' => '2026-07-01',
            'ends_on' => '2027-06-30',
            'is_active' => true,
        ]);

        $semester = Semester::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'Ganjil',
            'number' => 1,
            'starts_on' => '2026-07-01',
            'ends_on' => '2026-12-31',
            'is_active' => true,
        ]);

        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'username' => 'admin-'.uniqid(),
        ]);

        [$teacherUser, $teacher] = $this->makeTeacher();
        [$otherUser, $otherTeacher] = $this->makeTeacher();

        $class = SchoolClass::query()->create([
            'academic_year_id' => $year->id,
            'name' => 'X RPL 1',
            'level' => 'X',
        ]);

        $subject = Subject::query()->create([
            'code' => 'MTK'.random_int(100, 999),
            'name' => 'Matematika',
            'is_active' => true,
        ]);

        $student = Student::query()->create([
            'nis' => (string) random_int(100000, 999999),
            'name' => 'Siswa Nol',
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        ClassEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $year->id,
            'status' => EnrollmentStatus::Active,
            'started_on' => '2026-07-01',
        ]);

        $assignment = TeachingAssignment::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'academic_year_id' => $year->id,
            'semester_id' => $semester->id,
        ]);

        return compact(
            'year',
            'semester',
            'admin',
            'teacherUser',
            'teacher',
            'otherUser',
            'otherTeacher',
            'class',
            'subject',
            'student',
            'assignment'
        );
    }

    /**
     * @return array{0: User, 1: Teacher}
     */
    protected function makeTeacher(): array
    {
        $user = User::factory()->create([
            'role' => UserRole::Teacher,
            'username' => 'guru-'.uniqid(),
        ]);

        $teacher = Teacher::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'nip' => (string) random_int(1000000000, 1999999999),
        ]);

        return [$user, $teacher];
    }
}
