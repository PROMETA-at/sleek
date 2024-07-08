@props(['size' => 'md', 'color' => 'primary', 'outline' => false])
<button
  {{
    $attributes
      ->merge(['type' => 'button'])
      ->class([
        'btn',
        'btn-lg' => $size === 'lg',
        'btn-sm' => $size === 'sm',
        ...(
          !$outline
            ? ["btn-{$color}"]
            : ["btn-outline-{$color}"]
        ),
      ])
  }}
>{{ $slot }}</button>
