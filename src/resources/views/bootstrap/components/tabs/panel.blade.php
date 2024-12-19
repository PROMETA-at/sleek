@props(['name'])

<div id="{{$name}}" role="tabpanel" aria-labelledby="{{$name}}-tab" {{ $attributes->class(['tab-pane', 'show', 'active']) }}>
    {{ $slot }}
</div>
