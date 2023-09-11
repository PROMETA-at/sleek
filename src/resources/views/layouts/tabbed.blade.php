@extends($__data['sleek::page'] ?? 'sleek::layouts.page')

@section('body')
  <nav class="nav nav-{{ $__data['sleek.tabbed::style'] ?? 'pills' }} @if($__data['sleek.tabbed::fill'] ?? false) nav-fill @endif mb-2">
    @foreach($__data['sleek.tabbed::tabs'] as $tab)
      <a @class(['nav-link', 'active' => request()->url() === $tab['route']]) href="{{ $tab['route'] }}" hx-boost="true">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </nav>

  @fragment('tab')

  @yield('body')

{{--  Additionally to just re-yielding the body, we also yield a tab-section for the currently active tab, if any. --}}
{{--  This allows for a single-file setup where all tabs are defined in the same file. --}}
  @foreach($__data['sleek.tabbed::tabs'] as $key => $tab)
    @if($tab['route'] === request()->url())
      @yield("tab-$key")
    @endif
  @endforeach

  @endfragment
@overwrite
