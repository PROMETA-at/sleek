<?php namespace Prometa\Sleek\Views\Components;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class FormField extends \Illuminate\View\Component
{
    use ResolvesPrefixesFromContext;

    public string $originalName;

    public function __construct(
        public string  $name,
        public ?string $key = null,
        public ?string $i18nPrefix = null,
        public ?string $i18nResolutionStrategy = null,
        public string  $type = 'text',
        public ?string $label = null,
        public ?string $accessor = null,
        public mixed   $value = null,
        public array   $options = [],
        public bool    $multiple = false,
        public ?string $id = null,
        public string  $mode = 'radio',
        public bool    $floating = true,
        public ?string $placeholder = null,
    ) {
        $this->originalName = $name;

        $modelFromContext = static::factory()->getConsumableComponentData('model');
        $labelFactory = static::factory()->getConsumableComponentData('mkLabel');
        $this->resolvePrefixesFromContext($modelFromContext);

        $this->i18nResolutionStrategy ??= static::factory()->getConsumableComponentData('i18nResolutionStrategy', 'inherit-name');

        $nameFromContext = static::factory()->getConsumableComponentData('name');
        $this->name = implode('.', array_filter([$nameFromContext, $this->name]));
        if ($this->label === null) {
            if ($labelFactory) $this->label = $labelFactory($this);
            else if ($this->i18nResolutionStrategy === 'isolate-name') $this->label = __("$this->i18nPrefix.fields.{$this->originalName}");
            else $this->label = __("$this->i18nPrefix.fields.{$this->name}");
        }

        if (! $this->accessor) $this->accessor = $this->name;
        // Sometimes, a field might need to reference nested data, as part of a JSON-field for example.
        // This data can be referenced using dot-notation, but the field name needs to follow the urlencoded structure,
        // like field[sub][subsub].
        $this->name = with(explode('.', $this->name), fn ($parts) => $parts[0] . implode('', array_map(fn ($part) => "[$part]", array_slice($parts, 1))));
        if ($this->multiple) $this->name .= '[]';
        $this->id ??= $this->name;

        $this->value = old($this->name, $value ?? optional($modelFromContext, fn ($x) => data_get($x, $this->accessor)) ?? null);

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
        'textarea' => view('sleek::components.form-field.textarea'),
        'radio-group' => view('sleek::components.form-field.radio-group'),
        'button-group' => view('sleek::components.form-field.button-group'),
        'hidden' => view('sleek::components.form-field.hidden'),
        'custom' => fn ($data) => $data['slot']->toHtml(),
        default => view('sleek::components.form-field.input'),
      };
    }
}
