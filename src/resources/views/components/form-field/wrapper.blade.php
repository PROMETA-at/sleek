@aware(['name', 'label'])

<div class="mb-2">
  @if($label)
    <label for="{{ $name ?? $label->attributes->get('for') ?? '' }}" {{ ($label->attributes ?? new \Illuminate\View\ComponentAttributeBag())->class(['form-label']) }}>{{ $label }}</label>
  @endif

  @if(isset($before) || isset($after))
    <div class="input-group">
      {{ $before ?? null }}
      {{ $slot }}
      {{ $after ?? null }}
    </div>
  @else
    {{ $slot }}
  @endif

  @error($name)
  <div class="invalid-feedback">
    {{ $message }}
  </div>
  @enderror
</div>
