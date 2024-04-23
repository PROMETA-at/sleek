<x-sleek::form-field.wrapper>
  @forwardSlots
  <input type="{{ $type }}"
         id="{{ $id }}"
         name="{{ $name }}"
         value="{{ $value }}"
    {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
  />
</x-sleek::form-field.wrapper>
