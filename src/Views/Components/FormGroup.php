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
        public ?\Closure $mkLabel = null,
    )
    {
        $nameFromContext = static::factory()->getConsumableComponentData('name');
        if ($nameFromContext) $this->name = implode('.', [$nameFromContext, $this->name]);
        $i18nFromContext = static::factory()->getConsumableComponentData('i18nPrefix');
        if ($i18nFromContext !== null) $this->i18nPrefix = implode('.', [$i18nFromContext, $this->i18nPrefix]);
        $routeFromContext = static::factory()->getConsumableComponentData('routePrefix');
        if ($routeFromContext) $this->routePrefix = implode('.', [$routeFromContext, $this->routePrefix]);

        $this->mkLabel ??= static::factory()->getConsumableComponentData('mkLabel');
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
