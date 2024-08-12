<?php namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;

class FormGroup extends Component
{
    public function __construct(
        public readonly bool $passthrough = false,
        public ?string $name = null,
        public ?string $i18nPrefix = null,
        public ?string $routePrefix = null,
    )
    {
        $nameFromContext = static::factory()->getConsumableComponentData('name');
        if ($nameFromContext) $this->name = implode('.', [$nameFromContext, $this->name]);
        $i18nFromContext = static::factory()->getConsumableComponentData('i18nPrefix');
        if ($i18nFromContext) $this->i18nPrefix = $i18nFromContext;
        $routeFromContext = static::factory()->getConsumableComponentData('routePrefix');
        if ($routeFromContext) $this->routePrefix = $routeFromContext;
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        // TODO: implement actual styled form group when not passthrough - no need for now
        if ($this->passthrough || true) return fn ($data) => $data['slot']->toHtml();
    }
}
