<?php namespace Prometa\Sleek\Views\Components;

use function Prometa\Sleek\resolveKeyFromContext;

class FormField extends \Illuminate\View\Component
{
    public function __construct(
        public $name,
        public $key = null,
        public $type = 'text',
        public $label = null,
        public $value = null,
        public $options = null
    ) {
        $modelFromContext = static::factory()->getConsumableComponentData('model');

        // The key is used to automagically resolve translation entries and routes for detail and edit views.
        //  If not set, we try to resolve a reasonable key from the current route name.
        if (! $this->key) {
            $this->key = resolveKeyFromContext($modelFromContext);
        }

        if (! $this->label) $this->label = __("$this->key.fields.$name");
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('sleek::components.form-field');
    }
}
