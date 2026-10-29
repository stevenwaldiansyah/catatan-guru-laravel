<?php

namespace App\Policies;

use App\Enums\JournalStatus;
use App\Models\TeachingJournal;
use App\Models\User;

class TeachingJournalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->teacher !== null;
    }

    public function view(User $user, TeachingJournal $journal): bool
    {
        return $user->teacher?->id === $journal->teacher_id;
    }

    public function create(User $user): bool
    {
        return $user->teacher !== null;
    }

    public function update(User $user, TeachingJournal $journal): bool
    {
        return $user->teacher?->id === $journal->teacher_id;
    }

    public function delete(User $user, TeachingJournal $journal): bool
    {
        return $user->teacher?->id === $journal->teacher_id
            && $journal->status === JournalStatus::Draft;
    }
}
