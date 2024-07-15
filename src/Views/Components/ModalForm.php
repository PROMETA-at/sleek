<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
class ModalForm extends Component
{
    public function __construct(
        public $id,
        public $action,
        public $method,
        public $title,
        public $formId = null,
        public $formType = 'form',
        public $model = null,
        public $fields = null,
        public $enctype = 'application/x-www-form-urlencoded'
    )
    {
        $this->formId = uniqid();
    }

    public function render()
    {
        return view('sleek::components.modal-form');
    }

}
