<?php

namespace App\Models;

use App\Support\Days;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    protected $fillable = [
        'teaching_assignment_id', 'day_of_week', 'start_time', 'end_time', 'room', 'label',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class, 'teaching_assignment_id');
    }

    public function dayName(): string
    {
        return Days::name((int) $this->day_of_week);
    }

    public function timeRange(): string
    {
        return substr((string) $this->start_time, 0, 5).'–'.substr((string) $this->end_time, 0, 5);
    }

    public function sessionLabel(): string
    {
        return filled($this->label) ? (string) $this->label : $this->timeRange();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->whereHas('assignment', fn (Builder $assignment) => $assignment->where('teacher_id', $user->teacher?->id ?? 0));
    }
}
