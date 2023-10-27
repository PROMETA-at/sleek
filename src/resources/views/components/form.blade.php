@props(['method' => 'post', 'action'])
@php
    if (! in_array(strtolower($method), ['get', 'post'])) {
        $formMethod = $method;
        $method = 'post';
    }
@endphp


@once
  <script>
    document.addEventListener('alpine:init', () => {
      Alpine.data('sleek__form', () => ({
        loading: false,

        form: {
          ['@submit'](event) {
            // We're patching preventDefault here so users of this component can prevent form submission without
            // having to manually manage the loading state.
            const _preventDefault = event.preventDefault
            event.preventDefault = () => {
              this.loading = false
              _preventDefault.call(event)
            }

            this.loading = true
          }
        }
      }))
    })
  </script>
@endonce

<form x-data="sleek__form" x-bind="form" method="{{ $method }}" action="{{ $action }}" {{ $attributes }}>
    @isset($formMethod) @method($formMethod) @endisset
    @if(strtolower($method) === 'post') @csrf @endif
    {{ $slot }}
</form>
