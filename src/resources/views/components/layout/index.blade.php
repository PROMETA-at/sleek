@props(['document' => null, 'page' => null])

<x-dynamic-component :component="$document ?? $__data['sleek::document'] ?? 'sleek::document'">
    <x-dynamic-component :component="$page ?? $__data['sleek::page'] ?? 'sleek::page'">
        {{ $slot }}
    </x-dynamic-component>
</x-dynamic-component>
