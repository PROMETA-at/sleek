<x-sleek::form-field.wrapper>
  @php($selectedValue = $value)
  @foreach($options as $value => $label)
    <div class="form-check">
      <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $id }}-{{ $value }}" value="{{ $value }}" {{ $value === $selectedValue ? 'checked' : '' }}>
      <label class="form-check-label" for="{{ $name }}-{{ $value }}">
        {{ $label }}
      </label>
    </div>
  @endforeach
</x-sleek::form-field.wrapper>
