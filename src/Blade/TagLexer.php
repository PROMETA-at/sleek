<?php

namespace Prometa\Sleek\Blade;

/**
 * Single-pass character lexer for Blade's component/slot tag layer.
 *
 * It replaces the regex passes of Laravel's `ComponentTagCompiler` (and Sleek's overrides of them)
 * with one linear scan. The grammar below is a deliberate port of those patterns rather than a
 * re-derivation: the token stream must mark exactly the spans the regexes would have replaced,
 * warts included, so compiled output stays byte-identical.
 *
 * One wart is preserved deliberately, because it looks like a bug here: tag recognition is attempted
 * in the order the old passes ran — slot open, then self-closing component, then opening component —
 * since that order is what decides which grammar applies. A `<x-slot ... />` therefore lexes as a
 * *component* named `slot`, exactly as before.
 *
 * The grammars are otherwise near-identical: they differ in their terminator, and in two
 * alternatives the slot pattern never grew (the `:$var` shorthand, and `%` in attribute names).
 *
 * Anything that does not form a valid tag stays literal text, mirroring a regex non-match.
 */
class TagLexer
{
    /** Attribute grammar of the opening-component pattern. */
    protected const MODE_OPEN = 'open';

    /** Attribute grammar of the self-closing-component pattern. */
    protected const MODE_SELF_CLOSE = 'self-close';

    /** Attribute grammar of the slot-opening pattern. */
    protected const MODE_SLOT = 'slot';

    /** Characters PCRE's `\s` matches. */
    protected const WHITESPACE = " \t\n\r\f\v";

    /** Characters PCRE's `\w` matches, absent the unicode flag. */
    protected const WORD = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_';

    /** Component name charset: `[\w\-:.]`. */
    protected const COMPONENT_NAME = self::WORD.'-:.';

    /** Attribute name charset of the component grammars: `[\w\-:.@%]`. */
    protected const COMPONENT_ATTRIBUTE = self::COMPONENT_NAME.'@%';

    /** Attribute name charset of the slot grammar: `[\w\-:.@]`. */
    protected const SLOT_ATTRIBUTE = self::COMPONENT_NAME.'@';

    /**
     * Split the given template into literal text spans and component/slot tags.
     *
     * @return array<int, TagToken>
     */
    public function tokenize(string $value): array
    {
        $tokens = [];
        $length = strlen($value);
        $textStart = 0;
        $cursor = 0;

        while (($cursor = strpos($value, '<', $cursor)) !== false) {
            $token = $this->matchTag($value, $cursor, $length);

            if ($token === null) {
                $cursor++;

                continue;
            }

            if ($cursor > $textStart) {
                $tokens[] = new TagToken(TagToken::TEXT, substr($value, $textStart, $cursor - $textStart), $textStart);
            }

            $tokens[] = $token;
            $cursor = $textStart = $cursor + strlen($token->text);
        }

        if ($textStart < $length) {
            $tokens[] = new TagToken(TagToken::TEXT, substr($value, $textStart), $textStart);
        }

        return $tokens;
    }

    /**
     * Try to read a tag starting at the `<` on the given offset.
     */
    protected function matchTag(string $value, int $offset, int $length): ?TagToken
    {
        if (($value[$offset + 1] ?? '') === '/') {
            return $this->matchSlotClose($value, $offset, $length)
                ?? $this->matchComponentClose($value, $offset, $length);
        }

        $cursor = $this->skipWhitespace($value, $offset + 1, $length);

        if (($value[$cursor] ?? '') !== 'x' || ! in_array($value[$cursor + 1] ?? '', ['-', ':'], true)) {
            return null;
        }

        $cursor += 2;

        return $this->matchSlotOpen($value, $offset, $cursor, $length)
            ?? $this->matchComponentOpen($value, $offset, $cursor, $length);
    }

    // --- Opening tags ------------------------------------------------------------------------

    /**
     * `<x-slot:inline name="..." :name="..." ...>` — the slot-opening pattern.
     *
     * The three leading groups are optional and greedy, so a failure further right can give any of
     * them back and let the text be re-read as an ordinary attribute. The nested loops reproduce
     * that backtracking order: the leftmost group is surrendered last.
     *
     * @param  int  $nameStart  Offset just past the `x-` / `x:` prefix.
     */
    protected function matchSlotOpen(string $value, int $offset, int $nameStart, int $length): ?TagToken
    {
        if (substr_compare($value, 'slot', $nameStart, 4) !== 0) {
            return null;
        }

        foreach ($this->inlineNameCandidates($value, $nameStart + 4) as [$inlineName, $afterInline]) {
            foreach ($this->nameAttributeCandidates($value, $afterInline, $length, 'name=') as [$slotName, $afterName]) {
                foreach ($this->nameAttributeCandidates($value, $afterName, $length, ':name=') as [$boundName, $attributeStart]) {
                    $failed = [];
                    $end = $this->scanAttributes($value, $attributeStart, $length, self::MODE_SLOT, $failed);

                    if ($end === null) {
                        continue;
                    }

                    return new TagToken(
                        TagToken::SLOT_OPEN,
                        substr($value, $offset, $end + 1 - $offset),
                        $offset,
                        attributes: substr($value, $attributeStart, $end - $attributeStart),
                        inlineName: $inlineName,
                        slotName: $slotName,
                        boundName: $boundName,
                    );
                }
            }
        }

        return null;
    }

    /**
     * `<x-name ...>` or `<x-name ... />`.
     *
     * Self-closing is tried first because `compileSelfClosingTags()` ran before
     * `compileOpeningTags()`, and the two accept different attribute forms.
     *
     * @param  int  $nameStart  Offset just past the `x-` / `x:` prefix.
     */
    protected function matchComponentOpen(string $value, int $offset, int $nameStart, int $length): ?TagToken
    {
        $nameEnd = $this->runOf($value, $nameStart, self::COMPONENT_NAME);
        $name = substr($value, $nameStart, $nameEnd - $nameStart);

        foreach ([self::MODE_SELF_CLOSE, self::MODE_OPEN] as $mode) {
            $failed = [];
            $end = $this->scanAttributes($value, $nameEnd, $length, $mode, $failed);

            if ($end === null) {
                continue;
            }

            $selfClosing = $mode === self::MODE_SELF_CLOSE;

            return new TagToken(
                $selfClosing ? TagToken::COMPONENT_SELF_CLOSE : TagToken::COMPONENT_OPEN,
                substr($value, $offset, ($end + ($selfClosing ? 2 : 1)) - $offset),
                $offset,
                name: $name,
                attributes: substr($value, $nameEnd, $end - $nameEnd),
            );
        }

        return null;
    }

    /**
     * Candidate readings of `(?::(?<inlineName>\w+(?:-\w+)*))?`, greedy first.
     *
     * @return array<int, array{0: string, 1: int}> [name, offset after the group]
     */
    protected function inlineNameCandidates(string $value, int $cursor): array
    {
        $candidates = [];

        if (($value[$cursor] ?? '') === ':') {
            $end = $this->runOf($value, $cursor + 1, self::WORD);

            if ($end > $cursor + 1) {
                while (($value[$end] ?? '') === '-' && ($segment = $this->runOf($value, $end + 1, self::WORD)) > $end + 1) {
                    $end = $segment;
                }

                $candidates[] = [substr($value, $cursor + 1, $end - $cursor - 1), $end];
            }
        }

        $candidates[] = ['', $cursor];

        return $candidates;
    }

    /**
     * Candidate readings of `(?:\s+name=(?<name>("[^"]+"|'[^']+'|[^\s>]+)))?`, greedy first. The
     * returned value keeps its quotes — callers hand it back to the attribute parser verbatim.
     *
     * @return array<int, array{0: string, 1: int}> [raw value, offset after the group]
     */
    protected function nameAttributeCandidates(string $value, int $cursor, int $length, string $prefix): array
    {
        $candidates = [];
        $afterSpace = $this->skipWhitespace($value, $cursor, $length);

        if ($afterSpace > $cursor && substr_compare($value, $prefix, $afterSpace, strlen($prefix)) === 0) {
            $start = $afterSpace + strlen($prefix);
            $quote = $value[$start] ?? '';

            if ($quote === '"' || $quote === "'") {
                $close = strpos($value, $quote, $start + 1);

                if ($close !== false && $close > $start + 1) {
                    $candidates[] = [substr($value, $start, $close + 1 - $start), $close + 1];
                }
            }

            $end = $start;

            while ($end < $length && $value[$end] !== '>' && strpos(self::WHITESPACE, $value[$end]) === false) {
                $end++;
            }

            if ($end > $start) {
                $candidates[] = [substr($value, $start, $end - $start), $end];
            }
        }

        $candidates[] = ['', $cursor];

        return $candidates;
    }

    // --- Attribute sequence ------------------------------------------------------------------

    /**
     * Walk `(?:\s+ <attribute>)* \s*` from the given offset and return the offset of the tag
     * terminator, or null when no reading of the attributes reaches one.
     *
     * The regexes this ports are greedy with backtracking, so this is a depth-first search: consume
     * one more attribute if possible, and only fall back to the terminator when that dead-ends.
     * Positions known to dead-end are memoised, which keeps the search linear in the tag's length.
     *
     * @param  array<int, true>  $failed
     * @return int|null Offset of `>` (or of `/` in `/>`), whichever this mode terminates with.
     */
    protected function scanAttributes(string $value, int $cursor, int $length, string $mode, array &$failed): ?int
    {
        if (isset($failed[$cursor])) {
            return null;
        }

        $afterSpace = $this->skipWhitespace($value, $cursor, $length);

        if ($afterSpace > $cursor) {
            foreach ($this->attributeEnds($value, $afterSpace, $length, $mode) as $next) {
                $end = $this->scanAttributes($value, $next, $length, $mode, $failed);

                if ($end !== null) {
                    return $end;
                }
            }
        }

        if ($this->terminatesAt($value, $afterSpace, $mode)) {
            return $afterSpace;
        }

        $failed[$cursor] = true;

        return null;
    }

    /**
     * Offsets at which a single attribute starting on the given one could end, in the order the
     * regex alternation would try them.
     *
     * @return array<int, int>
     */
    protected function attributeEnds(string $value, int $cursor, int $length, string $mode): array
    {
        $ends = [];

        foreach (['@class', '@style'] as $directive) {
            if (substr_compare($value, $directive, $cursor, strlen($directive)) === 0) {
                $end = $this->matchBalancedParens($value, $cursor + strlen($directive), $length);

                if ($end !== null) {
                    $ends[] = $end;
                }

                break;
            }
        }

        if (($end = $this->matchEcho($value, $cursor, $length)) !== null) {
            $ends[] = $end;
        }

        // `:$var` shorthand — the slot pattern never grew this alternative.
        if ($mode !== self::MODE_SLOT && substr_compare($value, ':$', $cursor, 2) === 0) {
            if (($end = $this->runOf($value, $cursor + 2, self::WORD)) > $cursor + 2) {
                $ends[] = $end;
            }
        }

        return array_merge($ends, $this->plainAttributeEnds($value, $cursor, $length, $mode));
    }

    /**
     * `[\w\-:.@%]+ (= ("..." | '...' | [^'"=<>]+))?` — a name with an optional value.
     *
     * Unquoted values may contain whitespace, so a greedy read can swallow the following attributes
     * and the terminator alike. Every offset the value could be cut back to that leaves a terminator
     * or another attribute reachable is offered, longest first.
     *
     * @return array<int, int>
     */
    protected function plainAttributeEnds(string $value, int $cursor, int $length, string $mode): array
    {
        $charset = $mode === self::MODE_SLOT ? self::SLOT_ATTRIBUTE : self::COMPONENT_ATTRIBUTE;
        $nameEnd = $this->runOf($value, $cursor, $charset);

        if ($nameEnd === $cursor) {
            return [];
        }

        $ends = [];

        if (($value[$nameEnd] ?? '') === '=') {
            $start = $nameEnd + 1;
            $quote = $value[$start] ?? '';

            if ($quote === '"' || $quote === "'") {
                $close = strpos($value, $quote, $start + 1);

                if ($close !== false) {
                    $ends[] = $close + 1;
                }
            } else {
                $end = $start;

                while ($end < $length && strpos('\'"=<>', $value[$end]) === false) {
                    $end++;
                }

                if ($end > $start) {
                    $ends[] = $end;

                    for ($cut = $end - 1; $cut > $start; $cut--) {
                        if ($value[$cut] === '/' || strpos(self::WHITESPACE, $value[$cut]) !== false) {
                            $ends[] = $cut;
                        }
                    }
                }
            }
        }

        // The value group is optional: the attribute may also end right after its name.
        $ends[] = $nameEnd;

        return $ends;
    }

    /**
     * `{{ $... }}` — a spread attribute. Any variable will do, not just `$attributes`; Sleek widened
     * this so a component can forward a bag it assembled itself.
     */
    protected function matchEcho(string $value, int $cursor, int $length): ?int
    {
        if (substr_compare($value, '{{', $cursor, 2) !== 0) {
            return null;
        }

        $start = $this->skipWhitespace($value, $cursor + 2, $length);

        if (($value[$start] ?? '') !== '$') {
            return null;
        }

        $close = strpos($value, '}', $start + 1);

        if ($close === false || ($value[$close + 1] ?? '') !== '}') {
            return null;
        }

        return $close + 2;
    }

    /**
     * A parenthesised `@class(...)` / `@style(...)` argument list. Balanced-paren counting only —
     * the recursive subpattern this ports is likewise blind to quoting.
     *
     * @param  int  $cursor  Offset that must hold the opening paren.
     * @return int|null Offset just past the matching close paren.
     */
    protected function matchBalancedParens(string $value, int $cursor, int $length): ?int
    {
        if (($value[$cursor] ?? '') !== '(') {
            return null;
        }

        $depth = 0;

        for ($end = $cursor; $end < $length; $end++) {
            if ($value[$end] === '(') {
                $depth++;
            } elseif ($value[$end] === ')' && --$depth === 0) {
                return $end + 1;
            }
        }

        return null;
    }

    /**
     * Whether the tag's terminator sits on the given offset.
     *
     * The opening grammars refuse a `>` preceded by `/`, `=` or `-`; that guard is what keeps `=>`
     * and `->` inside unquoted bound expressions — and the `/` of a self-closing tag — from being
     * read as the end of an opening tag.
     */
    protected function terminatesAt(string $value, int $cursor, string $mode): bool
    {
        if ($mode === self::MODE_SELF_CLOSE) {
            return ($value[$cursor] ?? '') === '/' && ($value[$cursor + 1] ?? '') === '>';
        }

        return ($value[$cursor] ?? '') === '>'
            && ! in_array($value[$cursor - 1] ?? '', ['/', '=', '-'], true);
    }

    // --- Closing tags ------------------------------------------------------------------------

    /**
     * `</x-slot ...>`. The pattern this ports tolerates any junk before the `>` — and, because it
     * only anchors on the `x-slot` prefix, also swallows things like `</x-slotfoo>`.
     */
    protected function matchSlotClose(string $value, int $offset, int $length): ?TagToken
    {
        $cursor = $this->skipTagPrefix($value, $offset, $length);

        if ($cursor === null || substr_compare($value, 'slot', $cursor, 4) !== 0) {
            return null;
        }

        $close = strpos($value, '>', $cursor + 4);

        if ($close === false) {
            return null;
        }

        return new TagToken(TagToken::SLOT_CLOSE, substr($value, $offset, $close + 1 - $offset), $offset);
    }

    /**
     * `</x-name>`.
     */
    protected function matchComponentClose(string $value, int $offset, int $length): ?TagToken
    {
        $cursor = $this->skipTagPrefix($value, $offset, $length);

        if ($cursor === null) {
            return null;
        }

        $nameEnd = $this->runOf($value, $cursor, self::COMPONENT_NAME);
        $close = $this->skipWhitespace($value, $nameEnd, $length);

        if (($value[$close] ?? '') !== '>') {
            return null;
        }

        return new TagToken(
            TagToken::COMPONENT_CLOSE,
            substr($value, $offset, $close + 1 - $offset),
            $offset,
            name: substr($value, $cursor, $nameEnd - $cursor),
        );
    }

    /**
     * Skip `</\s*x[-:]` and return the offset of the component name, or null when it is not there.
     */
    protected function skipTagPrefix(string $value, int $offset, int $length): ?int
    {
        $cursor = $this->skipWhitespace($value, $offset + 2, $length);

        if (($value[$cursor] ?? '') !== 'x' || ! in_array($value[$cursor + 1] ?? '', ['-', ':'], true)) {
            return null;
        }

        return $cursor + 2;
    }

    // --- Character helpers -------------------------------------------------------------------

    protected function skipWhitespace(string $value, int $cursor, int $length): int
    {
        while ($cursor < $length && strpos(self::WHITESPACE, $value[$cursor]) !== false) {
            $cursor++;
        }

        return $cursor;
    }

    /**
     * Offset just past the run of characters drawn from the given charset.
     */
    protected function runOf(string $value, int $cursor, string $charset): int
    {
        return $cursor + strspn($value, $charset, $cursor);
    }
}
