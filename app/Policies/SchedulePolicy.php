<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Schedule $schedule): bool
    {
        return $user->teacher?->id === $schedule->assignment?->teacher_id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Schedule $schedule): bool
    {
        return false;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return false;
    }
}
