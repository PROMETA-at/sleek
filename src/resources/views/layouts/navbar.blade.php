@props(['navItems' => null])

<nav class="navbar navbar-expand-lg">
  <div class="{{ $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }}">

    <a class="navbar-brand d-flex" href="{{$__data['sleek::logo']['route'] ?? '/'}}">
        @if($__data['sleek::logo']['image'] ?? false)
            <img src="{{ $__data['sleek::logo']['image'] }}" alt="{{ env('APP_NAME') }}" height="25">
        @else
            {{ env('APP_NAME') }}
        @endif
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sleek-navbar" aria-controls="sleek-navbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="sleek-navbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        @foreach($navItems ?? $__data['sleek::navItems'] ?? [] as $key => $navItem)
          <li @class(['nav-item', 'dropdown' => array_key_exists('items', $navItem)])">
            <a @class(['nav-link', 'active' => request()->url() === $navItem['route'] ?? '#']) aria-current="page" href="{{ $navItem['route'] ?? '#' }}" hx-boost="true">{{ $navItem['label'] }}</a>
            @if(array_key_exists('items', $navItem))
              <ul>
                @foreach($navItem['items'] as $dropdownItem)
                  <li><a class="dropdown-item" href="{{ $dropdownItem['route'] }}">{{ $dropdownItem['label'] }}</a></li>
                @endforeach
              </ul>
            @endif
          </li>
        @endforeach
      </ul>

      @if(empty($__data['sleek::particle']))
          @unless(($__data['sleek::authentication'] ?? null) === false)
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
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
          @endunless

          @if(isset($__data['sleek::language']))
            <select class="form-select w-auto" onchange="window.location.href=`/lang/${this.value}`">
              @foreach($__data['sleek::language'] as $key => $lang)
                <option value="{{ $key }}" {{ App::getLocale() == $key ? 'selected' : '' }}>
                  {{ $lang }}
                </option>
              @endforeach
            </select>
          @endif
      @else
          @include($__data['sleek::particle'])
      @endif
    </div>
  </div>
</nav>
