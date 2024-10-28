@props(['icon'])

@if(isset($icon))
    @if(Str::startsWith($icon, 'bi:'))
        <!-- Explizites Bootstrap Icon -->
        <i class="bi bi-{{ Str::after($icon, 'bi:') }}"></i>
    @elseif(Str::startsWith($icon, 'component:'))
        <!-- Explizite Blade-Komponente -->
        <x-dynamic-component :component="Str::after($icon, 'component:')" />
    @elseif(Str::contains($icon, '::') || Str::contains($icon, '.'))
        <!-- Namespace-Komponente -->
        <x-dynamic-component :component="$icon" />
    @else
        <!-- Standard Bootstrap Icon -->
        <i class="bi bi-{{ $icon }}"></i>
    @endif
@endif
