<x-sleek::page>
  <div class="row justify-content-center">
    <div style="max-width: {{ $size ?? '80ch' }}">
      {{ $slot }}
    </div>
  </div>
</x-sleek::page>
