@props(['action', 'fields' => [], 'model' => null, 'method' => null, 'header', 'document' => null, 'page' => null])

<x-sleek::layout.centered :document="$document" :page="$page">
  @if (!isset($breadcrumbs) || $breadcrumbs === true)
    <x-sleek::breadcrumbs />
  @endif
  @if (isset($actions))
    <div class="mb-3">
      {{ $actions }}
    </div>
  @endif

  <x-sleek::card>
    @if(isset($header))
      <x-slot:header>
        {{ $header }}
      </x-slot:header>
    @endif

    @php
      $formAttributes = compact('action', 'fields', 'model', 'method');
    @endphp
    @component('Prometa\Sleek\Views\Components\EntityForm', 'sleek::entity-form', $formAttributes)
      @slot('slot', $slot)
    @endcomponentClass
  </x-sleek::card>
</x-sleek::layout.centered>
