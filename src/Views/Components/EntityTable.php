<?php namespace Prometa\Sleek\Views\Components;

use Illuminate\Support\Arr;
use function Prometa\Sleek\array_merge_recursive_distinct;

class EntityTable extends \Illuminate\View\Component
{
    use ResolvesPrefixesFromContext;

    public string $pageSizeName;
    public string $sortByName;
    public string $sortDirectionName;
    public string $pageName;

    public function __construct(
        public ?string  $key = null,
        public ?string  $i18nPrefix = null,
        public iterable $entities = [],
        public iterable $columns = [],
        public ?string  $size = null,
        public bool     $responsive = false,
        public bool|array  $sortable = false,
        public bool|string $navigation = true,
        public ?string  $scoped = null,
    ) {
        $this->resolvePrefixesFromContext($this->entities[0] ?? null);

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
                $value['label'] = __("{$this->i18nPrefix}.fields.{$value['name']}");
            }

            $value['sortable'] = $this->sortable === true || (is_array($this->sortable) && in_array($value['name'], $this->sortable));
        });
        $this->columns = array_values($this->columns);

        $prefix = $this->scoped ? $this->scoped.'.' : '';
        $this->pageSizeName = $prefix.'page-size';
        $this->sortByName = $prefix.'sort-by';
        $this->sortDirectionName = $prefix.'sort-direction';
        $this->pageName = $prefix.'page';
    }

    public function currentRoute(array $extra = []): string {
        $query = request()->query();
        $query = array_merge_recursive_distinct($query, $extra);
        return request()->fullUrlWithQuery($query);
    }

    public function sortedRoute(string $column): string {
        $prefix = $this->scoped ? $this->scoped.'.' : '';

        $params = [];
        Arr::set($params, $this->sortByName, $column);
        Arr::set($params, $this->sortDirectionName,
            (
                !request($this->sortDirectionName) || 
                request($this->sortByName) !== $column || 
                request($this->sortDirectionName) === 'desc'
            ) 
            ? 'asc' 
            : 'desc' 
        );

        return $this->currentRoute($params);
    }

    public function render()
    {
        return view('sleek::components.entity-table');
    }
}
