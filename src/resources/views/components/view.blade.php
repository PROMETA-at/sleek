@props(['document' => null, 'page' => null, 'layout' => null, 'nav-items' => null])

<x-dynamic-component :component="$document ?? $__data['sleek::document'] ?? 'sleek::document'" {{ $attributes->thatStartWith('document:')->trimPrefix('document:') }}>
  <x-dynamic-component :component="$page ?? $__data['sleek::page'] ?? 'sleek::page'" {{ $attributes->thatStartWith('page:')->trimPrefix('page:') }}>
    @foreach($__laravel_slots as $slotName => $s)
      @if(str_starts_with($slotName, 'page:'))
        @slot(substr($slotName, strlen('page:')), $s, $s->attributes)
      @endif
    @endforeach

    <x-dynamic-component :component="$layout ?? $__data['sleek::layout'] ?? 'sleek::layout'" {{ $attributes->thatStartWith('layout:')->trimPrefix('layout:') }}>
      {{ $slot }}
    </x-dynamic-component>
  </x-dynamic-component>
</x-dynamic-component>
