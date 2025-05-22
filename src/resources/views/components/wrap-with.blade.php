@use(Prometa\Sleek\Views\CallableComponentSlot)
@props(['component' => null, 'args' => []])

@capture
  {{ $slot }}
@into($content)

@if($component instanceof CallableComponentSlot)
  {{ $component($content, ...$args) }}
@else
  {{ $content }}
@endif
