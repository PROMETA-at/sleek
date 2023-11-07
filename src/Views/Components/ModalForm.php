<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
class ModalForm extends Component
{
    public function __construct(
        public $action,
        public $method,
        public $title,
        public $formType = 'form',
        public $model = null,
        public $fields = null
    )
    {
    }

    public function render()
    {
        return view('sleek::components.modal-form');
    }

}
