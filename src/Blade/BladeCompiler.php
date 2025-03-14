<?php namespace Prometa\Sleek\Blade;

class BladeCompiler extends \Illuminate\View\Compilers\BladeCompiler
{
    static array $slotStack = [];

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
        $isScoped = preg_match('/^(?<args>.*) bind ?\((?<bindings>.*?)\)( use ?\((?<uses>.*?)\))?/', substr($expression, 1, -1), $matches);

        if ($isScoped) {
            list($slot, $attributes) = explode(',', $matches['args']);
            if (empty($slot)) $slot = '"slot"';

            $uses = isset($matches['uses'])
                ? array_map('trim', explode(',', $matches['uses']))
                : [];
            $uses[] = '$__env';
            $uses = implode(', ', $uses);

            static::$slotStack[] = compact('isScoped', 'attributes');
            return implode('\n', [
                "<?php \$__env->slot({$slot}, function ({$matches['bindings']}) use ({$uses}) { ?>"
            ]);
        } else {
            static::$slotStack[] = compact('isScoped');
            return "<?php \$__env->slot{$expression}; ?>";
        }
    }

    /**
     * Compile the end-slot statements into valid PHP.
     *
     * @return string
     */
    protected function compileEndSlot()
    {
        $slotMeta = array_pop(static::$slotStack);

        return $slotMeta['isScoped']
            ? "<?php }, {$slotMeta['attributes']}); ?>"
            : '<?php $__env->endSlot(); ?>';
    }
}
