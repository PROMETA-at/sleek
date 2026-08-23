@props([
  'cols' => null,
  'gutter' => null,
  'gutterX' => null,
  'gutterY' => null,
  'align' => null,
  'justify' => null,
])

@php
  $toBootstrapValues = fn (array $values) => [
    'row-cols' => $values['cols'] ?? null,
    'g' => $values['gutter'] ?? null,
    'gx' => $values['gutter-x'] ?? null,
    'gy' => $values['gutter-y'] ?? null,
    'align-items' => $values['align'] ?? null,
    'justify-content' => $values['justify'] ?? null,
  ];

  $classes = \Prometa\Sleek\Views\BootstrapClassList::responsive(
    $toBootstrapValues([
      'cols' => $cols,
      'gutter' => $gutter,
      'gutter-x' => $gutterX,
      'gutter-y' => $gutterY,
      'align' => $align,
      'justify' => $justify,
    ]),
    collect($__laravel_slots)
      ->map(fn ($slot) => $toBootstrapValues($slot->attributes->getAttributes()))
      ->all(),
  );
@endphp

<div {{ $attributes->class(['row', ...$classes]) }}>
  {{ $slot }}
</div>
