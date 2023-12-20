<?php

namespace Prometa\Sleek\Views\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class EntityView extends Component
{
    use ResolvesPrefixesFromContext;
    public function __construct(
        public Model $model ,
        public ?string  $key = null,
        public ?string  $i18nPrefix = null,
        public iterable $fields = [],
        public ?string $title = null
    ) {
        $this->resolvePrefixesFromContext($model);
        array_walk($this->fields, function (&$value, $key) {
            if (is_string($value)) {
                $value = [
                    'name' => $value
                ];
            }

            if (is_string($key)) {
                if (! isset($value['name'])) $value['name'] = $key;
            }

            if (! isset($value['label'])) {
                $value['label'] = __("{$this->i18nPrefix}.fields.{$value['name']}");
            }
        });
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('sleek::components.entity-view');
    }
}
