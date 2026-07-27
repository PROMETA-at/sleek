<?php

namespace Tests\Fixtures;

/**
 * Test helper: records observable side effects so tests can assert whether a slot
 * body executed. Returns an empty string so it can be echoed inline in a Blade body.
 */
class SideEffect
{
    /** @var array<string, int> */
    public static array $hits = [];

    public static function reset(): void
    {
        static::$hits = [];
    }

    public static function hit(string $key): string
    {
        static::$hits[$key] = (static::$hits[$key] ?? 0) + 1;

        return '';
    }

    public static function count(string $key): int
    {
        return static::$hits[$key] ?? 0;
    }
}
