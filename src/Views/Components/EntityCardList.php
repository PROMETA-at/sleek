<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;

class EntityCardList extends Component
{
    use ResolvesPrefixesFromContext;

    public function __construct(
        public ?string  $key = null,
        public ?string  $i18nPrefix = null,
        public iterable $entities = [],
        public iterable $columns = [],
    ) {
        $this->resolvePrefixesFromContext($this->entities[0] ?? null);

        array_walk($this->columns, function (&$value, $key) {
            if (is_string($value)) {
                $value = [
                    'name' => $value
                ];
            }

            if (! isset($value['accessor'])) {
                $value['accessor'] = $value['name'];
                $value['name'] = explode('.', $value['name'])[0];
            }

            if (! isset($value['label'])) {
                $value['label'] = __("{$this->i18nPrefix}.fields.{$value['name']}");
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        return view("sleek::components.entity-card-list");
    }
}
