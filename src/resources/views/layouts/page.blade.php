@extends($__data['sleek::document'] ?? 'sleek::layouts.document')

@section('body')
  @if(is_string($__data['sleek::navbar'] ?? null))
    @include($__data['sleek::navbar'])
  @else
    @include('sleek::layouts.navbar')
  @endif

  <div class="{{ $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }}">
    @yield('body')
  </div>
@overwrite
