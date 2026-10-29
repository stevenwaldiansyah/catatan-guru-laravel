<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use SoftDeletes;

    protected $fillable = ['nis', 'nisn', 'name', 'gender', 'status'];

    public function setNisAttribute(?string $value): void
    {
        $this->attributes['nis'] = $this->blankToNull($value);
    }

    public function setNisnAttribute(?string $value): void
    {
        $this->attributes['nisn'] = $this->blankToNull($value);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ClassEnrollment::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(StudentNote::class);
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function currentEnrollment(): ?ClassEnrollment
    {
        return $this->enrollments()
            ->where('status', EnrollmentStatus::Active)
            ->with('schoolClass')
            ->latest('id')
            ->first();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $classIds = $user->teacher?->assignments()->pluck('class_id') ?? collect();

        return $query->whereHas('enrollments', function (Builder $enrollment) use ($classIds) {
            $enrollment->where('status', EnrollmentStatus::Active)->whereIn('class_id', $classIds);
        });
    }

    private function blankToNull(?string $value): ?string
    {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
