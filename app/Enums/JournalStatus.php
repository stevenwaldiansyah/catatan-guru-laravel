<?php

namespace App\Enums;

enum JournalStatus: string
{
    case Draft = 'draft';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Completed => 'Selesai',
        };
    }
}
