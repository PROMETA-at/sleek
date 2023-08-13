<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\ComponentAttributeBag;
use function is_string;
use function Prometa\Sleek\resolveKeyFromContext;

class EntityForm extends \Illuminate\View\Component
{
    public function __construct(
        public $action = null,
        public $key = null,
        public $model = null,
        public $fields = [],
        public $method = null
    ) {
        // The key is used to automagically resolve translation entries and routes for detail and edit views.
        //  If not set, we try to resolve a reasonable key from the current route name.
        if (! $this->key) {
            $this->key = resolveKeyFromContext($this->model);
        }

        if (! $this->method) {
            $this->method = !!$model ? 'put' : 'post';
        }

        // This default is based on resource controllers, where routes are automatically named based on their
        //  path and method.
        if (! $this->action) {
            if ($this->method == 'put' || $this->method == 'patch')
                $this->action = route("{$this->key}.update");
            else if ($this->method == 'post')
                $this->action = route("{$this->key}.create");
        }

        array_walk($this->fields, function (&$value, $key) {
            if (is_string($value)) {
                $value = [
                    'name' => $value
                ];
            }

            if (is_string($key)) {
                if (isset($value['name'])) $value['type'] = $value['name'];
                $value['name'] = $key;
            }

            if (!isset($value['type'])) $value['type'] = 'text';
            if (!isset($value['label'])) $value['label'] = __("$this->key.fields.{$value['name']}");

            if (!isset($value['attributes'])) $value['attributes'] = new ComponentAttributeBag([]);
            if (!$value['attributes'] instanceof ComponentAttributeBag)
                $value['attributes'] = new ComponentAttributeBag($value['attributes']);
        });
        $this->fields = array_values($this->fields);
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view('sleek::components.entity-form');
    }
}
