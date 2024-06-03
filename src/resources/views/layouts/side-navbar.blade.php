@props(['navItems' => null])

<nav id="sidebarMenu" class="navbar navbar-expand-lg shadow-lg p-3 d-flex" style="background-color: var(--bs-primary);">
    <a class="navbar-brand text-center" href="{{$__data['sleek::logo']['route'] ?? '/'}}">
        @if($__data['sleek::logo']['image'] ?? false)
            <img src="{{ $__data['sleek::logo']['image'] }}" alt="{{ env('APP_NAME') }}" height="40">
        @else
            {{ env('APP_NAME') }}
        @endif
    </a>
    <hr>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar" aria-controls="mobileNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mobileNavbar">
        <ul class="nav nav-pills flex-grow-1">
            @foreach($navItems ?? $__data['sleek::navItems'] ?? [] as $key => $navItem)
                @if(array_key_exists('items', $navItem))
                    <!-- Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown-{{ $key }}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ $navItem['label'] }}
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdown-{{ $key }}">
                            @foreach($navItem['items'] as $dropdownItem)
                                <li><a class="dropdown-item" href="{{ $dropdownItem['route'] }}">{{ $dropdownItem['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <!-- Normales Nav Item -->
                    <li class="nav-item">
                        <a class="nav-link {{ request()->url() === $navItem['route'] ? 'active' : '' }}" href="{{ $navItem['route'] }}">{{ $navItem['label'] }}</a>
                    </li>
                @endif
            @endforeach

            @if(empty($__data['sleek::particle']))
                @unless(($__data['sleek::authentication'] ?? null) === false)
                    <div class="mt-auto d-flex justify-content-between align-items-center w-100">
                        @if(isset($__data['sleek::language']))
                            <div class="dropup">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-translate"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                    @foreach($__data['sleek::language'] as $key => $lang)
                                        <li>
                                            <a class="dropdown-item" href="/lang/{{ $key }}" {{ App::getLocale() == $key ? 'selected' : '' }}>
                                                {{ $lang }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <ul class="navbar-nav">
                            @if(Auth::check())
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ $__data['sleek::authentication']['logout'] ?? route('logout') }}">
                                        <i class="bi bi-box-arrow-in-left"></i> Logout
                                    </a>
                                </li>
                            @else
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ $__data['sleek::authentication']['login'] ?? route('login') }}">
                                        <i class="bi bi-box-arrow-in-right"></i> Login
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endunless
            @else
                <div class="mt-auto">
                    @include($__data['sleek::particle'])
                </div>
            @endif

        </ul>
    </div>
</nav>
<style>

    .layout {
        display: grid;
        grid-template-columns: auto 1fr;
        min-height: 100vh;

    }
    .layout > #sidebarMenu {
        flex-direction: column;
    }
    #sidebarMenu ul.nav {
        flex-direction: column;
    }

    @media only screen and (min-width: 799px) {
        #sidebarMenu.navbar {
            justify-content: initial;
            align-items: initial;
        }
        #sidebarMenu.navbar .navbar-collapse {
            flex-basis: initial;
            align-items: initial;
        }
        .layout > #sidebarMenu {
            width: 250px;
        }
    }
    @media only screen and (max-width: 800px) {
        .layout {
            grid-template-columns: 1fr;
            grid-template-rows: auto 1fr;
        }

        .layout > #sidebarMenu {
            width: 100%;
            flex-direction: row;
        }
    }

    #sidebarMenu :is(.nav-item > .nav-link, .dropdown-toggle) {
        color: {{ $__data['sleek::theme']['colors']['primary-font-color'] ??  'white'}};

        &:hover {
            background-color: color-mix(in srgb, var(--bs-primary), black 12.5%);
            transition: background-color .35s ease;
        }
    }

    #sidebarMenu .nav-link:not(.dropdown-toggle) {
        position: relative;
    }

    #sidebarMenu .nav-link:not(.dropdown-toggle)::after {
        content: '';
        position: absolute;
        width: 0;
        height: 3px;
        display: block;
        margin-top: 5px;
        right: 0;
        background: var(--bs-info);
        transition: width .35s ease;
        -webkit-transition: width .35s ease;
    }

    #sidebarMenu .nav-link:not(.dropdown-toggle):hover::after {
        width: 100%;
        left: 0;
        background: var(--bs-info);
    }

    #sidebarMenu .nav-link.active:not(.dropdown-toggle)::after {
        width: 100%;
        left: 0;
        background: var(--bs-info);
    }

    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        background-color: color-mix(in srgb, {{ $__data['sleek::theme']['colors']['primary'] ?? 'var(--bs-primary)' }}, black 12.5%);
    }

</style>
