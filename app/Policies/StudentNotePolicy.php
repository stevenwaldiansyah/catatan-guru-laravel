<?php

namespace App\Policies;

use App\Models\StudentNote;
use App\Models\User;

class StudentNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->teacher !== null;
    }

    public function view(User $user, StudentNote $note): bool
    {
        return $user->teacher?->id === $note->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->teacher !== null;
    }

    public function update(User $user, StudentNote $note): bool
    {
        return $user->teacher?->id === $note->teacher_id;
    }

    public function delete(User $user, StudentNote $note): bool
    {
        return $user->teacher?->id === $note->teacher_id;
    }
}
