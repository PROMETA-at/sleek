<?php namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;

class FormGroup extends Component
{
    public function __construct(
        public readonly bool $passthrough = false,
        public readonly ?string $name = null,
    ) { }

    /**
     * @inheritDoc
     */
    public function render()
    {
        // TODO: implement actual styled form group when not passthrough - no need for now
        if ($this->passthrough || true) return fn ($data) => $data['slot']->toHtml();
    }
}
