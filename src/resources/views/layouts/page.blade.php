@extends($__data['sleek::document'] ?? 'sleek::layouts.document')

@section('body')
  @if(is_string($__data['sleek::navbar'] ?? null))
    @include($__data['sleek::navbar'])
  @else
    @include('sleek::layouts.navbar')
  @endif
  <x-sleek::alert></x-sleek::alert>
  <div class="{{ $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }} mt-2">
    @yield('body')
  </div>
@overwrite
