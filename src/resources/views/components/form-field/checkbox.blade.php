<div class="form-check mb-2">
  <input {{ $attributes->class(['form-check-input']) }} class="" type="checkbox" id="{{ $id }}" name="{{ $name }}" {{ $value ? 'checked' : '' }}>
  <label class="form-check-label" for="{{ $name }}">
    {{ $label }}
  </label>
</div>
