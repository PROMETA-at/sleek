<?php

namespace Tests\Fixtures;

use Illuminate\Support\Str;
use Prometa\Sleek\Blade\BladeCompiler;
use InvalidArgumentException;

/**
 * Verbatim copy of the regex-based ComponentTagCompiler as it stood before TagLexer replaced it.
 * Kept only as the reference side of ComponentTagLexerDifferentialTest — do not evolve it.
 */
class LegacyComponentTagCompiler extends \Illuminate\View\Compilers\ComponentTagCompiler
{
    /**
     * Compile the opening tags within the given string.
     *
     * @param  string  $value
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function compileOpeningTags(string $value)
    {
        return preg_replace_callback($this->componentOpeningPattern(), function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value);
    }

    public function compileSlots(string $value)
    {
        // Slots compile before component tags, so the raw <x-...> tags are still literal text. A
        // document-order scan attributes every slot to its nearest enclosing component, letting the
        // registry decide which slots compile as scoped (closure-backed) rather than eager.
        $registry = $this->blade instanceof BladeCompiler ? $this->blade->scopedSlotRegistry() : [];
        $attributions = $this->attributeSlotsToComponents($value, $registry);

        $value = preg_replace_callback($this->slotOpeningPattern(), function ($matches) use (&$attributions) {
            // Dequeue this slot's attribution — the scan visits slot tags in the same document order
            // preg_replace_callback does, so shifting keeps the two in lock-step.
            $attribution = array_shift($attributions);

            $name = $this->stripQuotes($matches['inlineName'] ?: $matches['name'] ?: $matches['boundName']);

            if (Str::contains($name, '-') && ! empty($matches['inlineName'])) {
                $name = Str::camel($name);
            }

            // If the name was given as a simple string, we will wrap it in quotes as if it was bound for convenience...
            if (! empty($matches['inlineName']) || ! empty($matches['name'])) {
                $name = "'{$name}'";
            }

            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);
            $hasSpreadAttributes = array_key_exists('attributes', $attributes);

            // If an inline name was provided and a name or bound name was *also* provided, we will assume the name should be an attribute...
            if (! empty($matches['inlineName']) && (! empty($matches['name']) || ! empty($matches['boundName']))) {
                $attributes = ! empty($matches['name'])
                    ? array_merge($attributes, $this->getAttributesFromAttributeString('name='.$matches['name']))
                    : array_merge($attributes, $this->getAttributesFromAttributeString(':name='.$matches['boundName']));
            }

            [$isScoped, $bindings] = $this->resolveSlotScoping($attribution, $attributes);

            if ($isScoped) {
                // `use` is obsolete — scope capture already carries every definition-site variable
                // into the body. Accept and discard it so existing templates keep compiling.
                unset($attributes['bind'], $attributes['use']);
            }

            if ($hasSpreadAttributes) {
                $spreadValue = $attributes['attributes'];
                unset($attributes['attributes']);
                $attributesString = '['.$this->attributesToString($attributes).']';
                $attributesString = <<<EOF
                    $spreadValue instanceof Illuminate\View\ComponentAttributeBag
                    ? {$spreadValue}->merge($attributesString)->getAttributes()
                    : array_merge($spreadValue ?? [], $attributesString)
                EOF;
            } else $attributesString = "[".$this->attributesToString($attributes)."]";

            if ($isScoped) {
                return " @slot({$name}, $attributesString bind ({$bindings}))";
            } else return " @slot({$name}, null, $attributesString)";
        }, $value);

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value);
    }

    /**
     * Decide whether a single slot compiles as a scoped closure, and with which bindings. A registry
     * match makes the slot scoped on its own; otherwise an explicit `bind` does.
     *
     * @return array{0: bool, 1: string} [isScoped, bindings]
     */
    protected function resolveSlotScoping(?array $attribution, array $attributes): array
    {
        $explicitBind = array_key_exists('bind', $attributes);

        if ($attribution === null) {
            return [$explicitBind, $explicitBind ? trim($attributes['bind'], "'") : ''];
        }

        if ($attribution['mode'] === 'zero') {
            if ($explicitBind) {
                throw new InvalidArgumentException(
                    "Slot [{$attribution['slot']}] of component [{$attribution['component']}] receives no arguments — remove the bind attribute."
                );
            }

            return [true, ''];
        }

        // Parameterized mode: the consumer must name what they receive.
        if (! $explicitBind) {
            throw new InvalidArgumentException(
                "Slot [{$attribution['slot']}] of component [{$attribution['component']}] is scoped and receives arguments — declare them, e.g. bind=\"{$attribution['params']}\"."
            );
        }

        return [true, trim($attributes['bind'], "'")];
    }

    /**
     * Scan component and slot tags in document order, attributing each slot open tag to its nearest
     * enclosing component. Returns one entry per slot open tag (in order): the registry match array
     * or null when the slot is not registry-scoped.
     *
     * @return array<int, ?array{mode: string, params: ?string, component: string, slot: string}>
     */
    protected function attributeSlotsToComponents(string $value, array $registry): array
    {
        $tokens = [];
        $slotOffsets = [];

        // Slot open tags — the tags we attribute.
        preg_match_all($this->slotOpeningPattern(), $value, $slotOpens, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($slotOpens as $m) {
            $slotOffsets[$m[0][1]] = true;
            $raw = ($m['inlineName'][0] ?? '') ?: ($m['name'][0] ?? '') ?: ($m['boundName'][0] ?? '');
            $tokens[] = ['offset' => $m[0][1], 'kind' => 'slot', 'name' => $this->stripQuotes($raw)];
        }

        // Slot close tags — recorded only so the component-close scan can skip them.
        preg_match_all('/<\/\s*x[\-\:]slot[^>]*>/', $value, $slotCloses, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($slotCloses as $m) {
            $slotOffsets[$m[0][1]] = true;
        }

        // Component open / self-closing / closing tags. A <x-slot...> tag also matches the component
        // patterns (same start offset); skip those so slots never land on the component stack.
        preg_match_all($this->componentOpeningPattern(), $value, $opens, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($opens as $m) {
            if (isset($slotOffsets[$m[0][1]])) continue;
            $tokens[] = ['offset' => $m[0][1], 'kind' => 'open', 'name' => $m[1][0]];
        }

        preg_match_all($this->componentSelfClosingPattern(), $value, $selfs, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($selfs as $m) {
            if (isset($slotOffsets[$m[0][1]])) continue;
            $tokens[] = ['offset' => $m[0][1], 'kind' => 'selfclose'];
        }

        preg_match_all('/<\/\s*x[-\:][\w\-\:\.]*\s*>/', $value, $closes, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($closes as $m) {
            if (isset($slotOffsets[$m[0][1]])) continue;
            $tokens[] = ['offset' => $m[0][1], 'kind' => 'close'];
        }

        usort($tokens, fn ($a, $b) => $a['offset'] <=> $b['offset']);

        $stack = [];
        $attributions = [];
        foreach ($tokens as $token) {
            switch ($token['kind']) {
                case 'open':
                    $stack[] = $token['name'];
                    break;
                case 'close':
                    array_pop($stack);
                    break;
                case 'slot':
                    $component = empty($stack) ? null : end($stack);
                    $attributions[] = $this->matchScopedSlot($registry, $component, $token['name']);
                    break;
                // self-closing components are recognised but never pushed.
            }
        }

        return $attributions;
    }

    /**
     * @return ?array{mode: string, params: ?string, component: string, slot: string}
     */
    protected function matchScopedSlot(array $registry, ?string $component, string $slotName): ?array
    {
        if ($component === null) return null;

        foreach ($registry as $entry) {
            if (Str::is($entry['componentPattern'], $component) && Str::is($entry['slotPattern'], $slotName)) {
                return [
                    'mode' => $entry['params'] === null ? 'zero' : 'param',
                    'params' => $entry['params'],
                    'component' => $component,
                    'slot' => $slotName,
                ];
            }
        }

        return null;
    }

    protected function componentOpeningPattern(): string
    {
        return "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                (\:\\\$)(\w+)
                            )
                            |
                            (?:
                                [\w\-:.@%]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";
    }

    protected function componentSelfClosingPattern(): string
    {
        return "/
            <
                \s*
                x[-\:]([\w\-\:\.]*)
                \s*
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$attributes(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                (\:\\\$)(\w+)
                            )
                            |
                            (?:
                                [\w\-:.@%]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
            \/>
        /x";
    }

    protected function slotOpeningPattern(): string
    {
        return "/
            <
                \s*
                x[\-\:]slot
                (?:\:(?<inlineName>\w+(?:-\w+)*))?
                (?:\s+name=(?<name>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+)))?
                (?:\s+\:name=(?<boundName>(\"[^\"]+\"|\\\'[^\\\']+\\\'|[^\s>]+)))?
                (?<attributes>
                    (?:
                        \s+
                        (?:
                            (?:
                                @(?:class)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                @(?:style)(\( (?: (?>[^()]+) | (?-1) )* \))
                            )
                            |
                            (?:
                                \{\{\s*\\\$(?:[^}]+?)?\s*\}\}
                            )
                            |
                            (?:
                                [\w\-:.@]+
                                (
                                    =
                                    (?:
                                        \\\"[^\\\"]*\\\"
                                        |
                                        \'[^\']*\'
                                        |
                                        [^\'\\\"=<>]+
                                    )
                                )?
                            )
                        )
                    )*
                    \s*
                )
                (?<![\/=\-])
            >
        /x";
    }

    /**
     * Parse the attribute bag in a given attribute string into its fully-qualified syntax.
     *
     * @param  string  $attributeString
     * @return string
     */
    protected function parseAttributeBag(string $attributeString)
    {
        $pattern = "/
            (?:^|\s+)                                        # start of the string or whitespace between attributes
            \{\{\s*(\\\$(?:[^}]+?(?<!\s))?)\s*\}\} # exact match of attributes variable being echoed
        /x";

        return preg_replace($pattern, ' :attributes="$1"', $attributeString);
    }
}
