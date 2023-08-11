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
        $isScoped = preg_match('/^(?<args>.*) bind ?\((?<bindings>.+)\)( use ?\((?<uses>.+)\))?/', substr($expression, 1, -1), $matches);
        static::$slotStack[] = compact('isScoped');

        if ($isScoped) {
            list($slot, $attributes) = explode(',', $matches['args']);
            $uses = isset($matches['uses'])
                ? array_map('trim', explode(',', $matches['uses']))
                : [];
            $uses[] = '$__env';
            $uses = implode(', ', $uses);

            $attributesSlot = "'".trim($slot, "'")."Attributes'";
            return implode('\n', [
                "<?php \$__env->slot({$attributesSlot}, null,{$attributes}); \$__env->endSlot(); ?>",
                "<?php \$__env->slot({$slot}, function ({$matches['bindings']}) use ({$uses}) { ?>"
            ]);
        } else {
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
            ? "<?php }); ?>"
            : '<?php $__env->endSlot(); ?>';
    }
}
