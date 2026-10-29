<?php

namespace App\Support;

class NoteCategories
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'akademik' => 'Akademik',
            'sikap' => 'Sikap',
            'kehadiran' => 'Kehadiran',
            'prestasi' => 'Prestasi',
            'lainnya' => 'Lainnya',
        ];
    }

    public static function label(?string $value): string
    {
        return self::options()[$value] ?? ($value ?: '-');
    }
}
