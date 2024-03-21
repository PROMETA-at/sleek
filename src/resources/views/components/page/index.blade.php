@if(isset($navbar))
  {{ $navbar }}
@elseif(is_string($__data['sleek::navItems'] ?? null))
  @include($__data['sleek::navItems'])
@else
  @include('sleek::layouts.navbar')
@endif

<div class="{{ $fluid ?? $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }} mt-2">
  {{ $slot }}
</div>
