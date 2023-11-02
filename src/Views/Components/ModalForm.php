<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
class ModalForm extends Component
{
    public function __construct(
        public $action,
        public $method,
        public $title)
    {
    }

    public function render()
    {
        return view('sleek::components.modal-form');
    }

}
