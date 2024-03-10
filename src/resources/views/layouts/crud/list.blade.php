@extends($__data['sleek::page'] ?? 'sleek::layouts.page')

@section('body')
  @if (!isset($breadcrumbs) || $breadcrumbs === true)
    <x-sleek::breadcrumbs />
  @endif
  <div class="mb-3">
    @yield('actions')
  </div>
  @yield('list')
@endsection
