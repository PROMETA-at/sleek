<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ env('APP_NAME') }}</title>


  @stack('assets')
  @vite($__data['sleek::assets']['vite'] ?? [])
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
<body>
  {{ $slot }}
</body>
</html>

