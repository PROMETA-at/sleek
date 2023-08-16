<?php namespace Prometa\Sleek\Views\Components;

class FormField extends \Illuminate\View\Component
{
    use ResolvesPrefixesFromContext;

    public function __construct(
        public string  $name,
        public ?string $key = null,
        public ?string $i18nPrefix = null,
        public string  $type = 'text',
        public ?string $label = null,
        public mixed   $value = null,
        public ?array  $options = null
    ) {
        $modelFromContext = static::factory()->getConsumableComponentData('model');
        $this->resolvePrefixesFromContext($modelFromContext);

        if (! $this->label) $this->label = __("$this->i18nPrefix.fields.$name");
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('sleek::components.form-field');
    }
}
