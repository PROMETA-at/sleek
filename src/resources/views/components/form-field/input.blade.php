<x-sleek::form-field.wrapper :floating="!in_array($type, ['file'])">
  @forwardSlots
  <x-bs::form-control type="{{ $type }}"
         id="{{ $id }}"
         name="{{ $name }}"
         value="{{ $value }}"
         placeholder="{{ $placeholder ?? $name }}"
    {{ $attributes->merge(compact('multiple'))->class(['is-invalid' => $errors->has($name)]) }}
  />
</x-sleek::form-field.wrapper>
