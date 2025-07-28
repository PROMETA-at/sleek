<?php namespace Prometa\Sleek\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;

class ModalForm extends Component
{
    public function __construct(
        public string $title,
        public $id = null,
        public $formId = null,
        public $formType = 'form',
        // Need to define the model as an explicit parameter,
        //  to make it available to the form fields as a consumable.
        public ?Model $model = null,
    )
    {
        $this->formId ??= uniqid('form-');
    }

    public function render()
    {
        return view('sleek::components.modal-form');
    }

}
