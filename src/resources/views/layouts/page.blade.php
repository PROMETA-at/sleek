@extends($__data['sleek::document'] ?? 'sleek::layouts.document')

@section('body')
    <div class="layout">
        @if(is_string($__data['sleek::navItems'] ?? null))
            @include($__data['sleek::navItems'])
        @else
            @include('sleek::layouts.side-navbar')
        @endif
        <x-sleek::alert :position="$__data['sleek::alert']['position'] ?? 'center'"></x-sleek::alert>
        <div class="{{ $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }} mt-2">
            @yield('body')
        </div>
    </div>
@overwrite
