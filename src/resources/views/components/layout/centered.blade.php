@props(['document' => null, 'page' => null])

<x-sleek::layout :document="$document" :page="$page ?? $__data['sleek::page'] ?? 'sleek::page.centered'">
  {{ $slot }}
</x-sleek::layout>
