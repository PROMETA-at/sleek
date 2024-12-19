<x-sleek::form-field.wrapper>
    <div class="btn-group" role="group">
        @php($selectedValue = is_bool($value) ? json_encode($value) : $value)
        @foreach($options as $value => $label)
            <input value="{{ $value }}"
                   type="{{ $mode }}"
                   class="btn-check"
                   name="{{ $name }}{{ $mode === 'checkbox' ? '[]' : '' }}"
                   id="{{ $name }}-{{ $value }}"
                   autocomplete="off"
                   @if($selectedValue === $value) checked @endif
            >
            <label class="btn btn-outline-primary" for="{{ $name }}-{{ $value }}">{{ $label }}</label>
        @endforeach
    </div>
</x-sleek::form-field.wrapper>
