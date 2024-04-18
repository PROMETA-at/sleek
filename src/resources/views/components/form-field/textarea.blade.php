<x-sleek::form-field.wrapper>
  <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ $value }}</textarea>
</x-sleek::form-field.wrapper>
