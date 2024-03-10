@extends('sleek::layouts.page')

@section('body')
  <div class="row justify-content-center">
    <div style="max-width: {{ $size ?? '80ch' }}">
      @yield('body')
    </div>
  </div>
@overwrite
