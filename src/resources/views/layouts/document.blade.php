<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>{{ env('APP_NAME') }}</title>

  @stack('assets')
  @vite($__data['sleek::assets']['vite'] ?? [])
</head>
<body>
  @yield('body')
</body>
</html>
