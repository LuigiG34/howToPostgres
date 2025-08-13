<?php

namespace App\Util;

final class PgArray
{
    /**
     * Build a Postgres array literal
     * Example: ['abc','Hello"World'] -> {"abc","Hello\"World"}
     */
    public static function fromList(array $values): string
    {
        $escaped = array_map(
            static fn(string $s) => str_replace(['\\','"'], ['\\\\','\\"'], $s),
            array_map('strval', $values)
        );
        return '{"' . implode('","', $escaped) . '"}';
    }
}
