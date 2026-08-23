<?php

namespace Prometa\Sleek\Blade;

/**
 * One unit of {@see TagLexer}'s output: either a span of literal text or a single component/slot tag.
 *
 * Tags carry the *raw* attribute substring rather than parsed attributes — parsing stays with
 * ComponentTagCompiler::getAttributesFromAttributeString(), the single source of truth for it.
 */
class TagToken
{
    public const TEXT = 'text';

    public const COMPONENT_OPEN = 'component-open';

    public const COMPONENT_SELF_CLOSE = 'component-self-close';

    public const COMPONENT_CLOSE = 'component-close';

    public const SLOT_OPEN = 'slot-open';

    public const SLOT_SELF_CLOSE = 'slot-self-close';

    public const SLOT_CLOSE = 'slot-close';

    /**
     * The nearest enclosing component of a slot-open token, filled in by {@see TagTree}. Null means
     * the slot has no enclosing component (or the token is not a slot).
     */
    public ?string $enclosingComponent = null;

    /**
     * @param  string  $kind  One of the class constants.
     * @param  string  $text  The exact source span this token covers.
     * @param  int  $offset  Byte offset of the span in the source.
     * @param  string  $name  Component name for component tags (`x-foo.bar` → `foo.bar`).
     * @param  string  $attributes  Raw attribute substring, whitespace included.
     * @param  string  $inlineName  Slot tags: the `<x-slot:inline-name>` form's name.
     * @param  string  $slotName  Slot tags: the raw `name=` value, quotes included.
     * @param  string  $boundName  Slot tags: the raw `:name=` value, quotes included.
     */
    public function __construct(
        public string $kind,
        public string $text,
        public int $offset,
        public string $name = '',
        public string $attributes = '',
        public string $inlineName = '',
        public string $slotName = '',
        public string $boundName = '',
    ) {
    }
}
