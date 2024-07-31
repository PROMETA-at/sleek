<x-sleek::form-field.wrapper :floating="!in_array($type, ['file'])">
  @forwardSlots
  <x-bs::form-control
    {{
      $attributes
        ->merge(compact('multiple', 'type', 'id', 'name', 'value'))
        ->merge(['placeholder' => $placeholder ?? $name])
        ->class(['is-invalid' => $errors->has($name)])
    }}
  />
</x-sleek::form-field.wrapper>
