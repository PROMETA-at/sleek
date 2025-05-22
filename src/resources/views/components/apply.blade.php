@props(['hoc' => null, 'base'])

@ensureSlotFor($hoc, true)

<x-dynamic-component :component="$base" {{ $attributes->merge($hoc->attributes->toArray()) }}>
  <x-wrap-with :component="$hoc">
    {{ $slot }}
  </x-wrap-with>
</x-dynamic-component>
