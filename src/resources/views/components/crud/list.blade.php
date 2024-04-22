@props(['entities', 'columns'])
@php
  if (!isset($table)) $table = new \Illuminate\View\ComponentSlot();
@endphp

@if (!isset($breadcrumbs) || $breadcrumbs === true)
  <x-sleek::breadcrumbs />
@endif
@if (isset($actions))
  <div class="mb-3">
    {{ $actions }}
  </div>
@endif

@php
  $tableAttributes =
    compact('entities', 'columns')
    + $table->attributes->getAttributes();
@endphp
@component('Prometa\Sleek\Views\Components\EntityTable', 'sleek::entity-table', $tableAttributes)
  @foreach(collect($__laravel_slots)->filter(fn ($s, $k) => Str::startsWith($k, 'column')) as $name => $slot)
    @slot($name, $slot)
  @endforeach
@endcomponentClass
