<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Arr;

class Alert extends Component
{
    public $message;
    public $type;
    public $iconType;

    public function __construct(
      public ?string $position = 'center'
    ) {
        $alert = Session::get('sleek_alert');
        $this->message = $alert['message'] ?? '';
        $this->type = $alert['type'] ?? 'info';

        $icons = [
            'primary' => 'bi-info-circle-fill',
            'secondary' => 'bi-info-circle-fill',
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            'info' => 'bi-info-circle-fill',
            'light' => 'bi-info-circle-fill',
            'dark' => 'bi-info-circle-fill',
        ];

        $this->iconType = Arr::get($icons, $this->type);
    }

    public function render()
    {
        return view('sleek::components.alert');
    }
}
