@aware(['name', 'label', 'type'])
@props(['floating' => false])

@php
  $inputGroup ??= new \Illuminate\View\ComponentSlot();
  if (is_string($label)) $label = new \Illuminate\View\ComponentSlot($label);
@endphp

@if(!$floating && isset($label))
  <label
    for="{{ $name ?? $label->attributes->get('for') ?? '' }}"
    {{ $label->attributes->class('form-label') }}
  >
    {{ $label }}
  </label>
@endif

<div class="input-group mb-2 @error($name) has-validation @enderror" {{ $inputGroup->attributes }}>
  {{ $before ?? null }}
  @if($floating)
    <x-bs::form-floating :class="$errors->has($name) ? 'is-invalid' : ''">
      @isset($label)
        @slot('label', $label, ['for' => $name ?? $label->attributes->get('for') ?? '', ...$label->attributes])
      @endisset
      {{ $slot }}
    </x-bs::form-floating>
  @else
    {{ $slot }}
  @endif
  {{ $after ?? null }}

  @error($name)
    <div class="invalid-feedback">
      {{ $message }}
    </div>
  @enderror
</div>
