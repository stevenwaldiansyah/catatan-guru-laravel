<?php

namespace App\Services;

use App\Models\Schedule;
use App\Models\SchoolSetting;
use App\Models\TeachingAssignment;

class ScheduleConflictChecker
{
    /**
     * @return list<string>
     */
    public function messages(int $assignmentId, int $day, string $start, string $end, ?string $room, ?int $ignoreId = null): array
    {
        $settings = SchoolSetting::query()->first();
        $assignment = TeachingAssignment::query()->findOrFail($assignmentId);
        $preventTeacher = $settings?->prevent_teacher_overlap ?? true;
        $preventClass = $settings?->prevent_class_overlap ?? true;
        $preventRoom = $settings?->prevent_room_overlap ?? false;

        $others = Schedule::query()
            ->with('assignment')
            ->where('day_of_week', $day)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->get();

        $messages = [];

        foreach ($others as $other) {
            if ($preventTeacher && $other->assignment->teacher_id === $assignment->teacher_id) {
                $messages[] = 'Guru sudah memiliki jadwal yang bertabrakan pada hari dan jam tersebut.';
            }

            if ($preventClass && $other->assignment->class_id === $assignment->class_id) {
                $messages[] = 'Kelas sudah memiliki jadwal yang bertabrakan pada hari dan jam tersebut.';
            }

            if ($preventRoom && filled($room) && filled($other->room) && strcasecmp((string) $room, (string) $other->room) === 0) {
                $messages[] = 'Ruang sudah digunakan pada hari dan jam tersebut.';
            }
        }

        return array_values(array_unique($messages));
    }
}
