@php
  $wrapper ??= new \Illuminate\View\ComponentSlot();
  $label ??= new \Illuminate\View\ComponentSlot();
  if (is_string($label)) $label = new \Illuminate\View\ComponentSlot($label);
@endphp

<div {{ $wrapper->attributes->class(['form-floating']) }}>
  {{ $slot }}
  <label {{ $label->attributes }}>{{ $label }}</label>
</div>
