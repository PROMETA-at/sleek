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
            // If the value is a string, we treat it as a shorthand for the field name and accessor being the same.
            if (is_string($value)) {
                $value = [
                    'accessor' => $value,
                    'name' => $value
                ];
            }

            // If the entry is associative, the key is treated as a shorthand for setting the field name.
            if (is_string($key)) {
                $value['name'] = $key;
            }

            if (! isset($value['accessor'])) {
                $value['accessor'] = $value['name'];
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
