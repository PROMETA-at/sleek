<?php

namespace Prometa\Sleek\Views;

final class BootstrapClassList
{
    /**
     * Build Bootstrap utility classes from base and responsive values.
     *
     * Each array maps a Bootstrap class prefix to its value. Base values are emitted
     * without a breakpoint, while responsive values include their breakpoint name.
     * Missing, false, and empty-string values are omitted.
     *
     * @param  array<string, mixed>  $baseValues  Bootstrap class prefixes mapped to base values
     * @param  array<string, array<string, mixed>>  $breakpoints  Breakpoints mapped to responsive values
     * @return array<int, string>
     *
     * @example
     * BootstrapClassList::responsive(
     *     ['col' => 12, 'offset' => null],
     *     ['md' => ['col' => 6, 'offset' => 2]],
     * );
     * // ['col-12', 'col-md-6', 'offset-md-2']
     */
    public static function responsive(array $baseValues, array $breakpoints): array
    {
        $classes = self::atBreakpoint($baseValues);

        foreach ($breakpoints as $breakpoint => $values) {
            $classes = [
                ...$classes,
                ...self::atBreakpoint($values, $breakpoint),
            ];
        }

        return $classes;
    }

    /**
     * Build Bootstrap classes for one set of values at an optional breakpoint.
     *
     * @param  array<string, mixed>  $values  Bootstrap class prefixes mapped to values
     * @param  string|null  $breakpoint  Breakpoint inserted between each prefix and value
     * @return array<int, string>
     */
    private static function atBreakpoint(array $values, ?string $breakpoint = null): array
    {
        $classes = [];

        foreach ($values as $prefix => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }

            $classes[] = implode('-', [
                $prefix,
                ...($breakpoint === null ? [] : [$breakpoint]),
                (string) $value,
            ]);
        }

        return $classes;
    }
}
