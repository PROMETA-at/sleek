<x-sleek::form-field.wrapper floating>
  <textarea
    id="{{ $id }}"
    name="{{ $name }}"
    placeholder="{{ $placeholder ?? $name }}"
    {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
  >{{ $value }}</textarea>
</x-sleek::form-field.wrapper>
