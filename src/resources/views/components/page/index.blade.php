<div class="layout">
    @if(isset($navbar))
      {{ $navbar }}
    @elseif(is_string($__data['sleek::navItems'] ?? null))
      @include($__data['sleek::navItems'])
    @else
      @include('sleek::layouts.side-navbar')
    @endif

    {{ $slot }}
</div>
