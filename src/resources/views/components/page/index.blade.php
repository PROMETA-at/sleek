<div class="layout {{ $__data['sleek::navPosition'] === 'side' ? 'layout-side' : 'layout-top' }}">
  <x-sleek::alert/>
  @if(isset($navbar))
    {{ $navbar }}
  @elseif(is_string($__data['sleek::navItems'] ?? null))
    @include($__data['sleek::navItems'])
  @else
    <x-sleek::navbar {{ $attributes->thatStartWith('nav:')->trimPrefix('nav:') }}>
      @foreach($__laravel_slots as $slotName => $s)
        @if(str_starts_with($slotName, 'nav:'))
          @slot(substr($slotName, strlen('nav:')), $s, $s->attributes)
        @endif
      @endforeach
    </x-sleek::navbar>
  @endif



  {{ $slot }}
</div>
