@props([
    'modal' => false,
    'label' => 'Close'
])

<button type="button" {{ $attributes->class(['btn-close']) }} @if($modal) data-bs-dismiss="modal" @endif aria-label="{{ $label }}"></button>
