<?php

namespace App\Support;

class Sanitize
{
    /** Escape LIKE wildcards and cap length so user input can't act as a pattern. */
    public static function like(?string $value, int $max = 100): string
    {
        $value = mb_substr((string) $value, 0, $max);

        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
