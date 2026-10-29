<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class SchoolClass extends Model
{
    protected $table = 'classes';

    protected $fillable = ['academic_year_id', 'name', 'level', 'major', 'homeroom_teacher_id'];

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function homeroomTeacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeachingAssignment::class, 'class_id');
    }

    public function journals(): HasMany
    {
        return $this->hasMany(TeachingJournal::class, 'class_id');
    }

    public function displayName(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Student>
     */
    public function activeStudents(): Collection
    {
        return Student::query()
            ->where('status', 'aktif')
            ->whereHas('enrollments', function ($query) {
                $query->where('class_id', $this->id)->where('status', EnrollmentStatus::Active);
            })
            ->orderBy('name')
            ->get();
    }
}
