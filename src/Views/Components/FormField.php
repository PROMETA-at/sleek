<?php

namespace Prometa\Sleek\Views\Components;

class FormField extends \Illuminate\View\Component
{
    public function __construct(
        public $name,
        public $type = 'text',
        public $label = null,
        public $value = null
    ) {
        if (! $this->label) $this->label = __("xx.fields.$name");
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('sleek::components.form-field');
    }
}
