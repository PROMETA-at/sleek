<x-sleek::form-field.wrapper>
  <x-slot:input-group style="flex-direction: column"></x-slot:input-group>
  @php($selectedValue = $value)
  @foreach($options as $value => $label)
    <x-bs::form-check type="radio" {{ $attributes->merge(compact('name', 'value'))->merge(['id' => $id.'-'.$value, 'selected' => $value === $selectedValue]) }}>
      <x-slot:label>
        {{ $label }}
      </x-slot:label>
    </x-bs::form-check>
  @endforeach
</x-sleek::form-field.wrapper>
