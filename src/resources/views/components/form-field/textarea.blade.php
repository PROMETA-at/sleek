<x-sleek::form-field.wrapper :floating="$floating ?? true">
  <textarea
    id="{{ $id }}"
    name="{{ $name }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
  >{{ $value }}</textarea>
</x-sleek::form-field.wrapper>
