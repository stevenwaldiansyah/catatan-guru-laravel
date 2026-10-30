<?php

namespace App\Models;

use App\Support\NoteCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentNote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'teacher_id', 'student_id', 'class_id', 'date', 'category', 'description', 'follow_up',
    ];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function categoryLabel(): string
    {
        return NoteCategories::label($this->category);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('teacher_id', $user->teacher?->id ?? 0);
    }
}
