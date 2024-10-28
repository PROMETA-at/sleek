@props(['items' => null])

<nav id="sidebarMenu" class="navbar navbar-expand-lg shadow-lg p-3 d-flex">
    <a class="navbar-brand text-center" style="margin-right: 0" href="{{$__data['sleek::logo']['route'] ?? '/'}}">
        @if($__data['sleek::logo']['image'] ?? false)
            <img src="{{ $__data['sleek::logo']['image'] }}" alt="{{ env('APP_NAME') }}" style="height: 2rem; max-width: 100%">
        @else
            {{ env('APP_NAME') }}
        @endif
    </a>
    <hr class="divider">

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mobileNavbar" aria-controls="mobileNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mobileNavbar">
        @if(isset($extra) || isset($__data['sleek::nav:extra']))
            <div class="extra">
                {{ $extra ?? view($__data['sleek::nav:extra']) }}
            </div>
            <hr class="divider">
        @endif


        <ul class="navbar-nav">
            @foreach($items ?? $__data['sleek::navItems'] ?? [] as $key => $navItem)
                @if(array_key_exists('items', $navItem))
                    <li class="nav-item">
                        <a class="nav-link text-nowrap {{ Str::startsWith(Request::url(), collect($navItem['items'])->pluck('route')->toArray()) ? 'active' : '' }}" href="#" data-bs-toggle="collapse" data-bs-target="#nav-collapse-{{ $key }}" aria-expanded="false" aria-controls="nav-collapse-{{ $key }}">
                            @if(isset($navItem['icon']))
                                <x-sleek::nav-icon :icon="$navItem['icon']" />&nbsp;
                            @endif
                            {{ $navItem['label'] }} &#x25BC;
                        </a>
                        <div id="nav-collapse-{{ $key }}" class="collapse {{ Str::startsWith(Request::url(), collect($navItem['items'])->pluck('route')->toArray()) ? 'show' : '' }}" style="padding-left: 1rem">
                            <ul class="nav flex-column">
                                @foreach($navItem['items'] as $subItem)
                                    <li class="nav-item">
                                        <a class="nav-link text-nowrap {{ Str::startsWith(Request::url(), $subItem['route']) ? 'active' : '' }}" href="{{ $subItem['route'] }}">
                                            @if(isset($subItem['icon']))
                                                <x-sleek::nav-icon :icon="$subItem['icon']" />&nbsp;
                                            @endif
                                            {{ $subItem['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link text-nowrap {{ Str::startsWith(Request::url(), $navItem['route']) ? 'active' : '' }}" href="{{ $navItem['route'] }}">
                            @if(isset($navItem['icon']))
                                <x-sleek::nav-icon :icon="$navItem['icon']" />&nbsp;
                            @endif
                            {{ $navItem['label'] }}
                        </a>
                    </li>
                @endif
            @endforeach
        </ul>

        <div class="account">
            @if(isset($account) || isset($__data['sleek::nav:account']))
                <hr class="divider">
                <div>
                    {{ $account ?? view($__data['sleek::nav:account']) }}
                </div>
            @else
                @unless(($__data['sleek::authentication'] ?? null) === false)
                    <hr class="divider">
                    <ul class="navbar-nav">
                        <li>
                            @if(isset($__data['sleek::language']))
                                <div class="dropup">
                                    <button class="nav-link dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
                        </li>
                    </ul>
                @endunless
            @endif
        </div>
    </div>
</nav>
<style>
    /* :is for low priority, so it can be easily overridden */
    :is(#sidebarMenu) {
      background-color: var(--bs-primary);
    }

    #sidebarMenu {
      container-type: inline-size;
    }

    @if($__data['sleek::navPosition'] === 'side')
        #mobileNavbar, #sidebarMenu ul.navbar-nav {
          flex-direction: column;
        }
        @media only screen and (min-width: 799px) {
          #sidebarMenu {
            flex-direction: column;

            justify-content: initial;
            align-items: initial;
            & .navbar-collapse {
              flex-basis: initial;
              align-items: initial;
            }

            & .account {
              margin-top: auto;
            }
          }
        }
    @else
        #sidebarMenu .account {
          order: 10;
        }
        @media only screen and (min-width: 799px) {
          #sidebarMenu .divider {
            display: none;
          }
          #sidebarMenu .extra {
            order: 3;
            margin-left: auto;
          }
        }
    @endif

    #sidebarMenu :is(.nav-item > .nav-link, .dropdown-toggle) {
      color: {{ $__data['sleek::theme']['colors']['primary-font-color'] ??  'white'}};

      &:hover {
        background-color: color-mix(in srgb, var(--bs-primary), black 12.5%);
        transition: background-color .35s ease;
      }
    }

    #sidebarMenu .nav-link:not(.dropdown-toggle) {
      position: relative;
      &::after {
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

      &:hover::after {
        width: 100%;
        left: 0;
        background: var(--bs-info);
      }
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
