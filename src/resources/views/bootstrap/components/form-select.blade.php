@props(['size' => 'md'])
<select
  {{
    $attributes
      ->class([
        'form-select',
        'form-control-lg' => $size === 'lg',
        'form-control-sm' => $size === 'sm',
      ])
  }}
>
  {{ $slot }}
</select>
