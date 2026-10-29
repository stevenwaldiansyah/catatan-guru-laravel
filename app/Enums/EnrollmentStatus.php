<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'active';
    case Moved = 'moved';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Moved => 'Pindah',
            self::Withdrawn => 'Keluar',
        };
    }
}
