<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\ClassEnrollment;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    public function place(Student $student, SchoolClass $class, ?string $date = null): void
    {
        $date ??= now()->toDateString();

        DB::transaction(function () use ($student, $class, $date) {
            $current = ClassEnrollment::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $class->academic_year_id)
                ->where('status', EnrollmentStatus::Active)
                ->lockForUpdate()
                ->first();

            if ($current && (int) $current->class_id === (int) $class->id) {
                return;
            }

            if ($current) {
                $current->update([
                    'status' => EnrollmentStatus::Moved,
                    'ended_on' => $date,
                ]);
            }

            ClassEnrollment::query()->create([
                'student_id' => $student->id,
                'class_id' => $class->id,
                'academic_year_id' => $class->academic_year_id,
                'status' => EnrollmentStatus::Active,
                'started_on' => $date,
            ]);
        });
    }
}
