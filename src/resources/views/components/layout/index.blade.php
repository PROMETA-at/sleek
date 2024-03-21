@props(['document' => null, 'page' => null, 'navItems' => null])

<x-dynamic-component :component="$document ?? $__data['sleek::document'] ?? 'sleek::document'">
    <x-dynamic-component :component="$page ?? $__data['sleek::page'] ?? 'sleek::page'" :navItems="$navItems ?? null">
        @isset($navbar)
            <x-slot:navbar>{{ $navbar }}</x-slot:navbar>
        @endisset
        {{ $slot }}
    </x-dynamic-component>
</x-dynamic-component>
