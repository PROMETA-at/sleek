@aware(['name', 'label'])

<div class="mb-2">
  @if($label)
    <label for="{{ $name ?? $label->attributes->get('for') ?? '' }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
  @endif

  {{ $slot }}

  @error($name)
  <div class="invalid-feedback">
    {{ $message }}
  </div>
  @enderror
</div>
