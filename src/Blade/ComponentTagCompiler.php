<?php

namespace Prometa\Sleek\Blade;

use Illuminate\Support\Str;

class ComponentTagCompiler extends \Illuminate\View\Compilers\ComponentTagCompiler
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
        $pattern = "/
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

        return preg_replace_callback($pattern, function (array $matches) {
            $this->boundAttributes = [];

            $attributes = $this->getAttributesFromAttributeString($matches['attributes']);

            return $this->componentString($matches[1], $attributes);
        }, $value);
    }

    public function compileSlots(string $value)
    {
        $pattern = "/
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

        $value = preg_replace_callback($pattern, function ($matches) {
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
            $isScoped = array_key_exists('bind', $attributes);

            // If an inline name was provided and a name or bound name was *also* provided, we will assume the name should be an attribute...
            if (! empty($matches['inlineName']) && (! empty($matches['name']) || ! empty($matches['boundName']))) {
                $attributes = ! empty($matches['name'])
                    ? array_merge($attributes, $this->getAttributesFromAttributeString('name='.$matches['name']))
                    : array_merge($attributes, $this->getAttributesFromAttributeString(':name='.$matches['boundName']));
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
                $bindings = trim($attributes['bind'], "'");
                unset($attributes['bind']);
                $uses = trim($attributes['use'] ?? '', "'");
                unset($attributes['use']);
                return " @slot({$name}, $attributesString bind ({$bindings})" . (strlen($uses) ? " use ({$uses})" : '') . ')';
            } else return " @slot({$name}, null, $attributesString)";
        }, $value);

        return preg_replace('/<\/\s*x[\-\:]slot[^>]*>/', ' @endslot', $value);
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
