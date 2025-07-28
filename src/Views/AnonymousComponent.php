<?php namespace Prometa\Sleek\Views;

use Illuminate\View\AnonymousComponent as Base;

class AnonymousComponent extends Base
{
    public function hasFlag(string $flag): bool 
    {
        $negFlagName = 'no'.ucfirst($flag);
        return !($this->attributes->has($negFlagName) && ($this->attributes->get($negFlagName) === true || $this->attributes->get($negFlagName) === 'true'))
            && ($this->attributes->has($flag) && ($this->attributes->get($flag) === true || $this->attributes->get($flag) === 'true'));
    }
}
