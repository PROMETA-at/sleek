@if(is_string($__data['sleek::navbar'] ?? null))
  @include($__data['sleek::navbar'])
@else
  @include('sleek::layouts.navbar')
@endif

<div class="{{ $fluid ?? $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }} mt-2">
  {{ $slot }}
</div>
