@props(['method' => 'post', 'action'])
@php
    if (! in_array(strtolower($method), ['get', 'post'])) {
        $formMethod = $method;
        $method = 'post';
    }
@endphp

<form method="{{ $method }}" action="{{ $action }}" {{ $attributes }}>
    @isset($formMethod) @method($formMethod) @endisset
    @if(strtolower($method) === 'post') @csrf @endif
    {{ $slot }}
</form>
