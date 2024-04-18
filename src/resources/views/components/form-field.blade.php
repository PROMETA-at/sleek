@aware(['model'])

@if($type === 'hidden')
    <input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes }} />
    @php return @endphp
@endif

<div class="mb-2">
    @if($label && !$fieldHasInlineLabel())
        <label for="{{ $name }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
    @endif

    @if($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ $value }}</textarea>
    @elseif($type === 'checkbox')
        <div class="form-check">
            <input {{ $attributes->class(['form-check-input']) }} class="" type="checkbox" id="{{ $id }}" name="{{ $name }}" {{ $value ? 'checked' : '' }}>
            <label class="form-check-label" for="{{ $name }}">
                {{ $label }}
            </label>
        </div>
    @elseif($type === 'radio-group')
        @php($selectedValue = $value)
        @foreach($options as $value => $label)
            <div class="form-check">
                <input class="form-check-input" type="radio" name="{{ $name }}" id="{{ $id }}-{{ $value }}" value="{{ $value }}" {{ $value === $selectedValue ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $name }}-{{ $value }}">
                    {{ $label }}
                </label>
            </div>
        @endforeach
        @elseif($type === 'select')
            @php($value = is_array($value) ? $value : [$value])
            <select id="{{ $id }}"
                    name="{{ $name }}"
                {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}
                {{ $multiple ? 'multiple' : '' }}
            >

                @isset($options)
                    @foreach($options as $optionValue => $label)
                        <option value="{{ $optionValue }}"
                            {{ in_array($optionValue, $value) ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                @else
                    {{ $slot }}
                @endisset
            </select>
        @else
        <input type="{{ $type }}"
               id="{{ $id }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
        />
    @endif

    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
