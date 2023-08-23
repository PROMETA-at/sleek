<?php namespace Prometa\Sleek\Views;

use Illuminate\View\ComponentAttributeBag;

class CallableComponentSlot
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
     * @var string
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
}
