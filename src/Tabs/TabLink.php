<?php namespace Prometa\Sleek\Tabs;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;

class TabLink implements Htmlable
{
    public string $href;
    public ComponentAttributeBag $attributes;

    public function __construct(
        ?string $href,
        public readonly string $content,
        private readonly ?TabsContext $context = null,
    ) {
        $this->href = $href ?? request()->urlWithQuery([]);
        $this->attributes = new ComponentAttributeBag();
    }

    public function withAttributes(array|callable $attributes): static {
        if (is_array($attributes)) {
            $this->attributes = $this->attributes->merge($attributes);
        } else if (is_callable($attributes)) {
            $this->attributes = $attributes($this->attributes);
        }

        return $this;
    }

    public function toHtml()
    {
        $linkAttributes = $this->attributes
            ->merge(['href' => $this->href]);

        return "<a {$linkAttributes}>{$this->content}</a>";
    }

    public function __toString()
    {
        return $this->toHtml();
    }

    private function context(): TabsContext {
        return $this->context ?? TabsContext::default();
    }
}