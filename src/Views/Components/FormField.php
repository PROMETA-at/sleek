<?php namespace Prometa\Sleek\Views\Components;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class FormField extends \Illuminate\View\Component
{
    use ResolvesPrefixesFromContext;

    public function __construct(
        public string  $name,
        public ?string $key = null,
        public ?string $i18nPrefix = null,
        public string  $type = 'text',
        public ?string $label = null,
        public ?string $accessor = null,
        public mixed   $value = null,
        public array  $options = [],
        public ?string $id = null,
    ) {
        $modelFromContext = static::factory()->getConsumableComponentData('model');
        $nameFromContext = static::factory()->getConsumableComponentData('name');
        $this->resolvePrefixesFromContext($modelFromContext);

        if ($nameFromContext) $this->name = implode('.', [$nameFromContext, $this->name]);
        if ($this->label === null) $this->label = __("$this->i18nPrefix.fields.$name");

        if (! $this->accessor) $this->accessor = $this->name;
        // Sometimes, a field might need to reference nested data, as part of a JSON-field for example.
        // This data can be referenced using dot-notation, but the field name needs to follow the urlencoded structure,
        // like field[sub][subsub].
        $this->name = with(explode('.', $this->name), fn ($parts) => $parts[0] . implode('', array_map(fn ($part) => "[$part]", array_slice($parts, 1))));
        $this->id ??= $this->name;

        $this->value = old($name, $value ?? optional($modelFromContext, fn ($x) => data_get($x, $this->accessor)) ?? '');

        // Y-m-d is the standard format expected by browsers for `date` or `datetime` input fields.
        // TODO: differentiate between time-less and time-ful formats.
        if ($this->value instanceof DateTime) $this->value = $this->value->format('Y-m-d');
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
      return match ($this->type) {
        'select' => view('sleek::components.form-field.select'),
        'checkbox' => view('sleek::components.form-field.checkbox'),
        'radio-group' => view('sleek::components.form-field.radio-group'),
        default => view('sleek::components.form-field.input')
      };
    }
}
