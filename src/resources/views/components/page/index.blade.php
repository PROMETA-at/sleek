@if(isset($navbar))
  {{ $navbar }}
@elseif(is_string($__data['sleek::navItems'] ?? null))
  @include($__data['sleek::navItems'])
@else
  @include('sleek::layouts.navbar')
@endif

{{ $slot }}
