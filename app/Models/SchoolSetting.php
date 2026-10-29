<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;

class SchoolSetting extends Model
{
    protected $fillable = [
        'school_name', 'npsn', 'address', 'phone', 'email',
        'active_academic_year_id', 'active_semester_id',
        'prevent_teacher_overlap', 'prevent_class_overlap', 'prevent_room_overlap',
    ];

    protected function casts(): array
    {
        return [
            'prevent_teacher_overlap' => 'boolean',
            'prevent_class_overlap' => 'boolean',
            'prevent_room_overlap' => 'boolean',
        ];
    }

    public function activeYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'active_academic_year_id');
    }

    public function activeSemester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'active_semester_id');
    }

    public static function displayName(): string
    {
        try {
            return static::query()->value('school_name') ?: 'EduJurnal';
        } catch (QueryException) {
            return 'EduJurnal';
        }
    }
}
