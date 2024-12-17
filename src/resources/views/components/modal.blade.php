@flags(['close'])

@ensureSlotFor($header)

<x-bs::modal {{ $attributes->merge(['fade' => 'true', 'centered' => true]) }}>
    @forwardSlots

    @isset($header)
        @slot('header', null, $header->attributes->getAttributes())
            {{ $header }}
            @flag('close') <x-bs::btn.close modal /> @endflag
        @endslot
    @endisset

    @unless($slot->isEmpty())
        <x-slot:body>
            {{ $slot }}
            @unless(isset($header))
                @flag('close') <x-bs::btn.close modal class="float-end" /> @endflag
            @endunless
        </x-slot:body>
    @endunless

    @isset($extra) {{ $extra }} @endisset
</x-bs::modal>
