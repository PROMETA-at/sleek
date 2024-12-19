@aware(['key', 'default'])
@props(['name'])

@php
    $activeTab = request($key) ?? $default;
@endphp

@if($activeTab === $name)
    <x-bs::tabs.panel :name="$name" {{ $attributes }}>
        {{ $slot }}
    </x-bs::tabs.panel>
@endif
