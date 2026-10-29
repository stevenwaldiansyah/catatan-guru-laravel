<?php

namespace App\Support;

class Search
{
    public static function like(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($value));

        return '%'.$escaped.'%';
    }
}
