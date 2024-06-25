@aware(['name', 'label', 'type'])

<div class="mb-2">
  @if($label)
    <label for="{{ $name ?? $label->attributes->get('for') ?? '' }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
  @endif

  <div class="input-group @error($name) is-invalid @enderror">
    {{ $before ?? null }}
    {{ $slot }}
    {{ $after ?? null }}
  </div>

  @error($name)
    <div class="invalid-feedback">
      {{ $message }}
    </div>
  @enderror
</div>
