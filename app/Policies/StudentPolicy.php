<?php

namespace App\Policies;

use App\Enums\EnrollmentStatus;
use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Student $student): bool
    {
        $classIds = $user->teacher?->assignments()->pluck('class_id') ?? collect();

        return $student->enrollments()
            ->where('status', EnrollmentStatus::Active)
            ->whereIn('class_id', $classIds)
            ->exists();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Student $student): bool
    {
        return false;
    }

    public function delete(User $user, Student $student): bool
    {
        return false;
    }
}
