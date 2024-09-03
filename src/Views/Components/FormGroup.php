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
        public ?string $i18nResolutionStrategy = null,
        public ?\Closure $mkLabel = null,
    )
    {
        $nameFromContext = static::factory()->getConsumableComponentData('name');
        if ($nameFromContext) $this->name = implode('.', array_filter([$nameFromContext, $this->name]));

        if (str_starts_with($this->i18nPrefix, 'isolate:')) {
          $this->i18nPrefix = substr($this->i18nPrefix, 8);
        } else {
          $i18nFromContext = static::factory()->getConsumableComponentData('i18nPrefix');
          if ($i18nFromContext !== null) $this->i18nPrefix = implode('.', array_filter([$i18nFromContext, $this->i18nPrefix]));
        }

        $routeFromContext = static::factory()->getConsumableComponentData('routePrefix');
        if ($routeFromContext) $this->routePrefix = implode('.', array_filter([$routeFromContext, $this->routePrefix]));

        $this->i18nResolutionStrategy ??= static::factory()->getConsumableComponentData('i18nResolutionStrategy');
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
