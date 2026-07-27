<?php namespace Prometa\Sleek\Blade;

class BladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
{
    static array $slotStack = [];

    /**
     * Registered scoped-slot patterns: each entry is
     * ['componentPattern' => string, 'slotPattern' => string, 'params' => ?string].
     *
     * @var array<int, array{componentPattern: string, slotPattern: string, params: ?string}>
     */
    protected array $scopedSlotRegistry = [];

    /**
     * Register a slot-name pattern of a component to compile as a scoped (closure-backed) slot.
     *
     * Zero-arg mode (no $params): matching slots compile to argument-less closures — their bodies
     * execute only when invoked, and nothing new enters scope. Parameterized mode ($params given):
     * consumers keep writing bind="..."; the string is used only as the suggestion in the
     * compile-time error when bind is missing — the compiler never injects those names.
     *
     * Registration must happen at provider boot, before any consumer template compiles. Because the
     * compiled output depends on the registry, changing registrations requires `php artisan view:clear`.
     */
    public function scopedSlots(string $componentPattern, string $slotPattern, ?string $params = null): void
    {
        $this->scopedSlotRegistry[] = compact('componentPattern', 'slotPattern', 'params');
    }

    /**
     * @return array<int, array{componentPattern: string, slotPattern: string, params: ?string}>
     */
    public function scopedSlotRegistry(): array
    {
        return $this->scopedSlotRegistry;
    }

    /**
     * Compile the component tags.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComponentTags($value)
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases, $this->classComponentNamespaces, $this
        ))->compile($value);
    }


    protected function compileSlot($expression)
    {
        $isScoped = preg_match(
            '/^(?<args>.*) bind ?\((?<bindings>.*?)\)$/',
            substr($expression, 1, -1),
            $matches
        );

        if (! $isScoped) {
            static::$slotStack[] = compact('isScoped');
            return "<?php \$__env->slot{$expression}; ?>";
        }

        // Split only on the first comma: the attributes array may itself contain commas.
        list($slot, $attributes) = array_pad(explode(',', $matches['args'], 2), 2, '');
        if (empty($slot)) $slot = '"slot"';

        // Every scoped slot carries its definition-site scope as a leading $__scope argument, so
        // bodies see the surrounding template variables without naming them.
        $bindings = trim($matches['bindings']);
        $params = $bindings === '' ? '$__scope' : "\$__scope, {$bindings}";

        static::$slotStack[] = compact('isScoped', 'attributes');

        return "<?php \$__env->slot({$slot}, function ({$params}) use (\$__env) { extract(\$__scope, EXTR_SKIP); ?>";
    }

    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSlot()
    {
        $slotMeta = array_pop(static::$slotStack);

        if (! $slotMeta['isScoped']) {
            return '<?php $__env->endSlot(); ?>';
        }

        // get_defined_vars() sits after the closing brace — back in template scope, so it snapshots
        // the definition-site variables once rather than per invocation.
        return "<?php }, {$slotMeta['attributes']}, get_defined_vars()); ?>";
    }
}
