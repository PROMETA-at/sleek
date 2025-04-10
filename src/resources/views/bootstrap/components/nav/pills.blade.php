@props(['orientation' => 'horizontal'])

<x-bs::nav
    {{
        $attributes
            ->class(['nav-pills', 'flex-column me-3' => $orientation === 'vertical'])
            ->merge($orientation === 'vertical' ? ['aria-orientation' => 'vertical'] : [])
    }}
>
    {{ $slot }}
</x-bs::nav>
