<x-sleek::form-field.wrapper floating>
  @forwardSlots
  <x-bs::form-control type="{{ $type }}"
         id="{{ $id }}"
         name="{{ $name }}"
         value="{{ $value }}"
         placeholder="{{ $placeholder ?? $name }}"
    {{ $attributes->class(['is-invalid' => $errors->has($name)]) }}
  />
</x-sleek::form-field.wrapper>
