@use(Prometa\Sleek\Views\CallableComponentSlot)
@props(['component' => null])

@capture
  {{ $slot }}
@into($content)

@if($component instanceof CallableComponentSlot)
  {{ $component($content) }}
@else
  {{ $content }}
@endif
