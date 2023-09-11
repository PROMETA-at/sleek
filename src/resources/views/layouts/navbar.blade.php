<nav class="navbar navbar-expand-lg">
  <div class="{{ $__data['sleek::fluid'] ?? true ? 'container-fluid' : 'container' }}">
    <a class="navbar-brand" href="/">{{ env('APP_NAME') }}</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sleek-navbar" aria-controls="sleek-navbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="sleek-navbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        @foreach($__data['sleek::navbar'] ?? [] as $key => $navItem)
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
    </div>
  </div>
</nav>
