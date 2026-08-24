<?php namespace Prometa\Sleek\Views;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\View\ComponentAttributeBag;

use function Prometa\Sleek\capture;

class CallableComponentSlot implements Htmlable
{
    /**
     * The slot attribute bag.
     *
     * @var \Illuminate\View\ComponentAttributeBag
     */
    public $attributes;

    /**
     * The slot contents.
     *
     * @var callable
     */
    protected $callable;

    /**
     * Create a new slot instance.
     *
     * @param  callable  $callable
     * @param  array     $attributes
     * @return void
     */
    public function __construct($callable, $attributes = [])
    {
        $this->callable = $callable;

        if ($attributes instanceof ComponentAttributeBag) $attributes = $attributes->all();
        $this->withAttributes($attributes);
    }

    /**
     * Set the extra attributes that the slot should make available.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function withAttributes(array $attributes)
    {
        $this->attributes = new ComponentAttributeBag($attributes);

        return $this;
    }

    public function __invoke() {
        return call_user_func($this->callable, ...func_get_args());
    }

    /**
     * Render the slot by invoking it with no arguments.
     *
     * Only meaningful for zero-argument (deferred) slots; invoking a
     * parameterized slot without its arguments is a usage error regardless.
     *
     * @return string
     */
    public function toHtml()
    {
        return capture($this->callable);
    }
}
