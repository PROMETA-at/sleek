@props(['multiple' => false])
@php
    if (!is_array($value)) $value = [$value];
@endphp

<x-sleek::form-field.wrapper :floating="$floating ?? true">
  @forwardSlots
  <select id="{{ $id }}"
          name="{{ $name }}"
    {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}
    {{ $multiple ? 'multiple' : '' }}
  >
    @foreach($options as $optionValue => $label)
      <option value="{{ $optionValue }}"
        {{ in_array($optionValue, $value) ? 'selected' : '' }}>
        {{ $label }}
      </option>
    @endforeach
    {{ $slot }}
  </select>
</x-sleek::form-field.wrapper>
