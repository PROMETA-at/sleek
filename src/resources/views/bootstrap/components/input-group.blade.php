@props(['size' => 'md'])
<div
  {{
    $attributes
      ->class([
        'input-group',
        'input-group-lg' => $size === 'lg',
        'input-group-sm' => $size === 'sm',
      ])
  }}
>
  {{ $slot }}
</div>
