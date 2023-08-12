@aware(['model'])

<div class="mb-2">
    <label for="{{ $name }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
    <input type="{{ $type }}"
           id="{{ $name }}"
           name="{{ $name }}"
           class="form-control @error($name) invalid @enderror"
           value="{{ old($name) ?? $value ?? optional($model, fn ($x) => $x->{$name}) ?? '' }}"
           {{ $attributes->class(['form-control', 'invalid' => isset($errors->{$name})]) }}
    />
    @error($name)
        <div class="invalid-feedback">
            {{ $errors[$name] }}
        </div>
    @enderror
</div>
