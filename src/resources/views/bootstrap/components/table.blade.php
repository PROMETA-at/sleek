@props([
  'striped' => false,
  'hover' => false,
  'border' => null,
  'responsive' => false,
  'color' =>  null,
  'size' => 'md',
])

<table
  {{
    $attributes
      ->class([
        'table' => !$responsive,
        'table-hover' => $hover,
        'table-responsive' => $responsive === true,
        ...(
          is_string($responsive)
          ? ["table-responsive-{$responsive}"]
          : []
        ),
        'table-lg' => $size === 'lg',
        'table-sm' => $size === 'sm',
        'table-striped' => $striped === true,
        'table-striped-columns' => $striped === 'columns',
        'table-bordered' => $border === true,
        'table-borderless' => $border === false,

        ...(
          is_string($color)
            ? ["table-{$color}"]
            : []
        ),
      ])
  }}
>
  {{ $slot }}
</table>
