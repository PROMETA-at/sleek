@props(['navbar' => null, 'navItems' => null])

<x-sleek::page :navbar="$navbar" :navItems="$navItems">
  <div class="row justify-content-center">
    <div style="max-width: {{ $size ?? '80ch' }}">
      {{ $slot }}
    </div>
  </div>
</x-sleek::page>
