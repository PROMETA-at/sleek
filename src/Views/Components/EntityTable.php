<?php namespace Prometa\Sleek\Views\Components;

class EntityTable extends \Illuminate\View\Component
{
    public function __construct(
        public $key = null,
        public $entities = [],
        public $columns = [],
        public $size = null,
        public $responsive = false
    ) {
        // The key is used to automagically resolve translation entries and routes for detail and edit views.
        //  If not set, we try to resolve a reasonable key from the current route name.
        if (! $this->key) {
            $this->key = explode('.', request()->route()->getName())[0] ?? null;
        }

        array_walk($this->columns, function (&$value, $key) {
            $originalIsArray = is_array($value);

            // If the value is a string, we treat it as a shorthand setting the field name.
            if (is_string($value)) {
                $value = [
                    'name' => $value
                ];
            }

            // If the entry is associative, the key is treated as a shorthand for setting the field name.
            if (is_string($key)) {
                // If the originally passed value wasn't an array, we assume a shape like `name` => `accessor`.
                //  Since we moved the value to the `name` key previously, we now need to re-assign it to `accessor`.
                if (!$originalIsArray) {
                    $value['accessor'] = $value['name'];
                    unset($value['name']);
                }

                // If we don't have a name already, the key must be the name.
                if (! isset($value['name'])) $value['name'] = $key;
                // Otherwise, we assume a shape like `accessor` => ["name" => `name`, ...] and assign the key to the
                //  `accessor` key.
                else if (! isset($value['accessor'])) $value['accessor'] = $key;
                // The key never overwrites existing fields on the column definition!
            }

            // If we don't have an explicit accessor set, we assume that the field name is used as and accessor.
            //  In this case, we also assume that the field name is simply the first part of the accessor.
            if (! isset($value['accessor'])) {
                $value['accessor'] = $value['name'];
                $value['name'] = explode('.', $value['name'])[0];
            }

            if (! isset($value['label'])) {
                $value['label'] = __("{$this->key}.fields.{$value['name']}");
            }
        });
        $this->columns = array_values($this->columns);
    }

    public function render()
    {
        return view('sleek::components.entity-table');
    }
}
