<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ env('APP_NAME') }}</title>


  @stack('assets')
  @vite($__data['sleek::assets']['vite'] ?? [])
  @foreach($__data['sleek::assets']['favicon'] ?? [] as $faviconLink)
      <link rel="{{ $faviconLink['rel'] ?? 'icon' }}" href="{{ $faviconLink['href'] }}" type="{{ $faviconLink['type'] }}"/>
  @endforeach
  @foreach($__data['sleek::assets'] ?? [] as $key => $url)
    @if(!is_numeric($key)) @continue @endif
    @if(str_ends_with($url, '.css'))
      <link rel="stylesheet" href="{{ $url }}">
    @elseif(str_ends_with($url, '.js'))
      <script type="module" src="{{ $url }}"></script>
    @endif
  @endforeach
  @if(isset($assets))
    {{ $assets }}
  @endif

<style>
    :root {
        @isset($__data['sleek::theme']['colors'])
            @foreach($__data['sleek::theme']['colors'] as $key => $color)
                --bs-{{ $key }}: {{ $color }};
            @endforeach
         @endisset
    }
</style>
</head>
<body {{ $attributes }}>
  {{ $slot }}
  @stack('body-end')
</body>
</html>

