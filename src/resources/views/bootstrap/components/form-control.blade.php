@props(['type' => 'text', 'size' => 'md'])
<input
  type="{{ $type }}"
  {{
    $attributes
      ->class([
        'form-control',
        'form-control-lg' => $size === 'lg',
        'form-control-sm' => $size === 'sm',
      ])
  }}
>
