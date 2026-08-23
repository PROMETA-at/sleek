<?php

namespace Prometa\Sleek\Blade;

use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Compiles Blade's component and slot tags from a lexed token stream instead of the parent's regex
 * passes. {@see TagLexer} finds the tags, {@see TagTree} pairs them, and this class emits — reusing
 * the parent's semantic helpers (`componentClass()`, `componentString()`,
 * `getAttributesFromAttributeString()`, `attributesToString()`) unchanged.
 *
 * The lexer buys two things the regexes could not: slots know their enclosing component by
 * construction rather than by replaying a second scan in lock-step, and tag boundaries survive
 * quotes, echoes and nested parens without a wall of lookarounds. Emission stays the parent's.
 */
class ComponentTagCompiler extends \Illuminate\View\Compilers\ComponentTagCompiler
{
    /**
     * Compile the component and slot tags within the given string.
     *
     * @param  string  $value
     * @return string
     */
    public function compile(string $value)
    {
        $tokens = (new TagLexer)->tokenize($value);

        (new TagTree)->attribute($tokens);

        return $this->emit($tokens);
    }

    /**
     * Walk the tokens in document order, replacing each tag with its compiled form.
     *
     * @param  array<int, TagToken>  $tokens
     */
    protected function emit(array $tokens): string
    {
        $registry = $this->blade instanceof BladeCompiler ? $this->blade->scopedSlotRegistry() : [];

        $compiled = '';

        foreach ($tokens as $token) {
            $compiled .= match ($token->kind) {
                TagToken::TEXT => $token->text,
                TagToken::COMPONENT_OPEN => $this->emitComponent($token),
                TagToken::COMPONENT_SELF_CLOSE => $this->emitComponent($token)."\n@endComponentClass##END-COMPONENT-CLASS##",
                TagToken::COMPONENT_CLOSE => ' @endComponentClass##END-COMPONENT-CLASS##',
                TagToken::SLOT_OPEN => $this->emitSlot($token, $registry),
                // Delimit @endslot from immediately following text without rendering whitespace.
                TagToken::SLOT_SELF_CLOSE => $this->emitSlot($token, $registry).' @endslot<?php ?>',
                TagToken::SLOT_CLOSE => ' @endslot',
            };
        }

        return $compiled;
    }

    protected function emitComponent(TagToken $token): string
    {
        $this->boundAttributes = [];

        return $this->componentString($token->name, $this->getAttributesFromAttributeString($token->attributes));
    }

    /**
     * Emit a slot opening as either a scoped (closure-backed) or an eager `@slot` directive. The
     * registry decides which, keyed on the slot's enclosing component.
     *
     * @param  array<int, array{componentPattern: string, slotPattern: string, params: ?string}>  $registry
     */
    protected function emitSlot(TagToken $token, array $registry): string
    {
        $name = $this->stripQuotes($token->inlineName ?: $token->slotName ?: $token->boundName);

        $attribution = $this->matchScopedSlot($registry, $token->enclosingComponent, $name);

        if (Str::contains($name, '-') && ! empty($token->inlineName)) {
            $name = Str::camel($name);
        }

        // If the name was given as a simple string, we will wrap it in quotes as if it was bound for convenience...
        if (! empty($token->inlineName) || ! empty($token->slotName)) {
            $name = "'{$name}'";
        }

        $this->boundAttributes = [];

        $attributes = $this->getAttributesFromAttributeString($token->attributes);
        $hasSpreadAttributes = array_key_exists('attributes', $attributes);

        // If an inline name was provided and a name or bound name was *also* provided, we will assume the name should be an attribute...
        if (! empty($token->inlineName) && (! empty($token->slotName) || ! empty($token->boundName))) {
            $attributes = ! empty($token->slotName)
                ? array_merge($attributes, $this->getAttributesFromAttributeString('name='.$token->slotName))
                : array_merge($attributes, $this->getAttributesFromAttributeString(':name='.$token->boundName));
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
     * @param  array<int, array{componentPattern: string, slotPattern: string, params: ?string}>  $registry
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
