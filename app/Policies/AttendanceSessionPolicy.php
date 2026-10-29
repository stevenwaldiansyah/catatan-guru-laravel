<?php

namespace App\Policies;

use App\Models\AttendanceSession;
use App\Models\User;

class AttendanceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->teacher !== null;
    }

    public function view(User $user, AttendanceSession $attendance): bool
    {
        return $user->teacher?->id === $attendance->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->teacher !== null;
    }

    public function update(User $user, AttendanceSession $attendance): bool
    {
        return $user->teacher?->id === $attendance->teacher_id;
    }

    public function delete(User $user, AttendanceSession $attendance): bool
    {
        return $user->teacher?->id === $attendance->teacher_id;
    }
}
