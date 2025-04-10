@props(['key'])

<div {{ $attributes->class(['tab-content']) }} id="{{ $key }}-tab-content">
    {{ $slot }}
</div>
