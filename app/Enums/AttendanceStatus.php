<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'hadir';
    case Permitted = 'izin';
    case Sick = 'sakit';
    case Absent = 'alpa';

    public function label(): string
    {
        return match ($this) {
            self::Present => 'Hadir',
            self::Permitted => 'Izin',
            self::Sick => 'Sakit',
            self::Absent => 'Alpa',
        };
    }

    /**
     * @return list<self>
     */
    public static function options(): array
    {
        return [self::Present, self::Permitted, self::Sick, self::Absent];
    }
}
