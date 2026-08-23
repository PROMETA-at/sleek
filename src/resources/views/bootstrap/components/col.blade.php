@props([
  'span' => null,
  'offset' => null,
  'order' => null,
  'align' => null,
])

@php
  $toBootstrapValues = fn (array $values) => [
    'col' => $values['span'] ?? null,
    'offset' => $values['offset'] ?? null,
    'order' => $values['order'] ?? null,
    'align-self' => $values['align'] ?? null,
  ];

  $classes = \Prometa\Sleek\Views\BootstrapClassList::responsive(
    $toBootstrapValues(compact('span', 'offset', 'order', 'align')),
    collect($__laravel_slots)
      ->map(fn ($slot) => $toBootstrapValues($slot->attributes->getAttributes()))
      ->all(),
  );
@endphp

<div {{ $attributes->class([
  'col' => !collect($classes)->contains(fn ($class) => str_starts_with($class, 'col-')),
  ...$classes,
]) }}>
  {{ $slot }}
</div>
