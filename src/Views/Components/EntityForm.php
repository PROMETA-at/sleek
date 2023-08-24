<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\ComponentAttributeBag;

class EntityForm extends \Illuminate\View\Component
{
    use ResolvesPrefixesFromContext;

    public function __construct(
        public ?string  $action = null,
        public ?string  $key = null,
        public ?string  $i18nPrefix = null,
        public ?string  $routePrefix = null,
        public ?Model   $model = null,
        public iterable $fields = [],
        public ?string  $method = null
    ) {
        $this->resolvePrefixesFromContext($this->model);

        if (! $this->method) {
            $this->method = !!$model ? 'put' : 'post';
        }

        // This default is based on resource controllers, where routes are automatically named based on their
        //  path and method.
        if (! $this->action) {
            if ($this->method == 'put' || $this->method == 'patch')
                $this->action = route("$this->routePrefix.update", [$this->model]);
            else if ($this->method == 'post')
                $this->action = route("$this->routePrefix.store");
        }

        array_walk($this->fields, function (&$value, $key) {
            if (is_string($value)) {
                $value = [
                    'name' => $value
                ];
            }

            // This check handles shortcut-definitions like `name` => `type`.
            // Since we set `type` to the "name" key above, we now need to transfer it over to the "type" key.
            // However, we never override an existing "type" key, to not override any explicit definition.
            if (is_string($key)) {
                if (isset($value['name']) && !isset($value['type'])) $value['type'] = $value['name'];
                $value['name'] = $key;
            }

            // We use `array_key_exists` here instead of the usual `exists` to allow users to selectively fall back
            // to the default by setting these fields to `null`.
            if (!array_key_exists('i18nPrefix', $value) && isset($this->i18nPrefix)) $value['i18nPrefix'] = $this->i18nPrefix;
            if (!array_key_exists('routePrefix', $value) && isset($this->routePrefix)) $value['routePrefix'] = $this->routePrefix;

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
