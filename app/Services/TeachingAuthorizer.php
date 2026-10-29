<?php

namespace App\Services;

use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TeachingAuthorizer
{
    public function allows(?User $user, int $classId, int $subjectId, int $semesterId): bool
    {
        if (! $user?->is_active) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $teacherId = $user->teacher?->id;
        if (! $teacherId) {
            return false;
        }

        return TeachingAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('semester_id', $semesterId)
            ->exists();
    }

    public function assignments(User $user): Builder
    {
        $query = TeachingAssignment::query()->with(['teacher', 'schoolClass', 'subject', 'semester.academicYear']);

        if ($user->isTeacher()) {
            $query->where('teacher_id', $user->teacher?->id ?? 0);
        }

        return $query;
    }
}
