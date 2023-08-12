<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\ComponentAttributeBag;

class EntityForm extends \Illuminate\View\Component
{
    public function __construct(
        public $action = '',
        public $model = null,
        public $fields = [],
        public $method = null
    ) {
        if (! $this->method) {
            $this->method = !!$model ? 'put' : 'post';
        }

        array_walk($this->fields, function (&$value, $key) {
            $this->normalizeFieldData($value, $key);
        });
        $this->fields = array_values($this->fields);
    }

    protected function normalizeFieldData(&$data, $key = null) {
        if (is_string($data)) {
            $data = [
                'name' => $data
            ];
        }

        if (is_string($key)) {
            if (isset($data['name'])) $data['type'] = $data['name'];
            $data['name'] = $key;
        }

        if (! isset($data['type'])) $data['type'] = 'text';
        if (! isset($data['label'])) $data['label'] = __("$key.fields.{$data['name']}");

        if (! isset($data['attributes'])) $data['attributes'] = new ComponentAttributeBag([]);
        if (! $data['attributes'] instanceof ComponentAttributeBag)
            $data['attributes'] = new ComponentAttributeBag($data['attributes']);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('sleek::components.entity-form');
    }
}
