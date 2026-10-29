<?php

namespace App\Support;

class Days
{
    /**
     * ISO weekday numbers used by schedules. Sunday is intentionally absent.
     *
     * @return array<int, string>
     */
    public static function options(): array
    {
        return [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    }

    public static function name(int $day): string
    {
        return self::options()[$day] ?? '-';
    }
}
