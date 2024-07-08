@props(['type' => 'checkbox', 'inline' => false])

@php
  // TODO:
  //  - Button styles


  $wrapper ??= new \Illuminate\View\ComponentSlot();
  $label ??= new \Illuminate\View\ComponentSlot();
  if (is_string($label)) $label = new \Illuminate\View\ComponentSlot($label);
@endphp

<div
  {{
    $wrapper->attributes
      ->class([
        'form-check',
        'form-switch' => $type === 'switch',
        'form-check-inline' => $inline
      ])
  }}
>
  <input {{ $attributes->merge(compact('type'))->class(['form-check-input']) }}>
  <label {{ $label->attributes->merge(['for' => $attributes->get('id')])->class(['form-check-label']) }}>
    {{ $label }}
  </label>
</div>
