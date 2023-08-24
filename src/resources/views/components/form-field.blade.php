@aware(['model'])

<div class="mb-2">
    <label for="{{ $name }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
    @unless($type == 'select')
        <input type="{{ $type }}"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ $value }}"
               {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}
        />
    @else
        <select id="{{ $name }}"
                name="{{ $name }}"
                {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }}
        >
            @isset($options)
                @php($selectedValue = $value)
                @foreach($options as $value => $label)
                    <option value="{{ $value }}" {{ $value == $selectedValue ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            @else
                {{ $slot }}
            @endisset
        </select>
    @endunless
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
