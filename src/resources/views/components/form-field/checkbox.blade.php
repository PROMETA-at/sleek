@php
  if (is_string($label)) $label = new \Illuminate\View\ComponentSlot($label);
@endphp

<x-bs::form-check {{ $attributes->merge(compact('id', 'name', 'value'))->merge(['checked' => !!$value]) }}>
  @slot('label', $label, $label->attributes)
</x-bs::form-check>
