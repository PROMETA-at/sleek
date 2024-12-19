@aware(['key', 'default'])
@props(['name', 'label' => null, 'icon' => null])

@php
    $activeTab = request($key) ?? $default;
    $displayLabel = $label ?? ucfirst($name);
@endphp

<x-bs::tabs.header-item {{ $attributes }}>
    <a class="nav-link @if($activeTab === $name) active @endif"
       href="{{ request()->fullUrlWithQuery([$key => $name]) }}">
        @if($icon)
            <x-sleek::nav-icon :icon="$icon" />
        @endif
        {{ $displayLabel }}
    </a>
</x-bs::tabs.header-item>
