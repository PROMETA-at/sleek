<?php namespace Prometa\Sleek\Tabs;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;
use Stringable;

class Tab implements Htmlable, Stringable
{
    public ComponentAttributeBag $attributes;
    public readonly TabLink $link;
    public readonly string $id;

    public function __construct(
        public readonly string $key,
        public readonly string $content,
        string $href,
        string $label,
        public readonly bool $active = false,
        private readonly ?TabsContext $context = null,
    ) {
        $this->id = "{$this->context()->keyField}-{$this->key}";
        $this->attributes = new ComponentAttributeBag();
        $this->link = new TabLink($href, $label, $context);
    }

    public function withAttributes(array|callable $attributes): static {
        if (is_array($attributes)) {
            $this->attributes = $this->attributes->merge($attributes);
        } else if (is_callable($attributes)) {
            $this->attributes = $attributes($this->attributes);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        return $this->active ? $this->withContainer($this->content) : $this->empty();
    }

    public function empty(): string
    {
        return $this->withContainer('');
    }

    private function withContainer(string $content): string
    {
        $containerAttributes = $this->attributes
            ->merge(['id' => $this->id]);
        return "<div {$containerAttributes}>{$content}</div>";
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    private function context(): TabsContext {
        return $this->context ?? TabsContext::default();
    }
}