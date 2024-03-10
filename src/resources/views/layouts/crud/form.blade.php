@extends($__data['sleek::page'] ?? 'sleek::layouts.page.centered')

@section('body')
  @if (!isset($breadcrumbs) || $breadcrumbs === true)
    <x-sleek::breadcrumbs />
  @endif
  <div class="mb-3">
    @yield('actions')
  </div>
  <x-sleek::card>
    <x-slot:header>
      @yield('header')
    </x-slot:header>

    @yield('form')
  </x-sleek::card>
@endsection
