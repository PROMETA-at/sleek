<div class="layout">
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

@once
  <style>
    .layout {
      display: grid;
      grid-template-columns: 1fr;
      grid-template-rows: auto 1fr;
      min-height: 100vh;
    }

    @if($__data['sleek::navPosition'] === 'side')
      @media only screen and (min-width: 799px) {
        .layout {
          grid-template-columns: auto 1fr;
          grid-template-rows: initial;

          & > nav {
            min-width: 20ch;
            position: sticky;
            top: 0;
            max-height: 100vh;
          }
        }
      }
    @endif
  </style>
@endonce
