@if($__data['sleek::navPosition'] === 'side')
    <div class="layout"> @endif
        @if(isset($navbar))
            {{ $navbar }}
        @elseif(is_string($__data['sleek::navItems'] ?? null))
            @include($__data['sleek::navItems'])
        @elseif($__data['sleek::navPosition'] === 'side')
            @include('sleek::components.navbar.side-navbar')
        @elseif($__data['sleek::navPosition'] === 'top')
            @include('sleek::components.navbar.top-navbar')
        @endif

        {{ $slot }}
        @if($__data['sleek::navPosition'] === 'side') </div>
@endif
