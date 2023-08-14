@aware(['model'])

<div class="mb-2">
    <label for="{{ $name }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
    @unless($type == 'select')
        <input type="{{ $type }}"
               id="{{ $name }}"
               name="{{ $name }}"
               value="{{ old($name) ?? $value ?? optional($model, fn ($x) => $x->{$name}) ?? '' }}"
               {{ $attributes->class(['form-control', 'invalid' => isset($errors->{$name})]) }}
        />
    @else
        <select id="{{ $name }}"
                name="{{ $name }}"
                {{ $attributes->class(['form-select', 'invalid' => isset($errors->{$name})]) }}
        >
            @isset($options)
                @php($selectedValue = old($name) ?? $value ?? optional($model, fn ($x) => $x->{$name}) ?? '')
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
            {{ $errors[$name] }}
        </div>
    @enderror
</div>
