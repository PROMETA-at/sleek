@props(['reactivity' => false])
<x-bs::card {{ $attributes->class(['sleek-reactivity' => $reactivity]) }}>
    @isset($header)
        @php($attributes = $header->attributes->getAttributes())
        @slot('header', null, $attributes)
            {{$header}}
        @endslot
    @endisset

    <x-slot:body>
        {{$slot}}
    </x-slot:body>

    @isset($footer)
        @php($attributes = $footer->attributes->getAttributes())
        @slot('footer', null, $attributes)
        {{$footer}}
        @endslot
    @endisset
</x-bs::card>
@once
    @if($reactivity)
        <style>
            .sleek-reactivity {
                box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
                transition: 0.3s;
            }

            .sleek-reactivity:hover {
                box-shadow: 0 8px 16px 0 rgba(0,0,0,0.3);
            }
        </style>
    @endif
@endonce
