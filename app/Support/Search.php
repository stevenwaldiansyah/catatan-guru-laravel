<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Search
{
    private static function like(string $value): string
    {
        $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($value));

        return '%'.$escaped.'%';
    }

    /**
     * LIKE with an explicit escape character so MySQL, PostgreSQL, and SQLite
     * all treat "%" and "_" in the term as literals.
     *
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public static function where(Builder $query, string $column, string $value, string $boolean = 'and'): Builder
    {
        return $query->whereRaw(
            $query->getGrammar()->wrap($query->qualifyColumn($column)).' like ? escape ?',
            [self::like($value), '\\'],
            $boolean,
        );
    }
}
