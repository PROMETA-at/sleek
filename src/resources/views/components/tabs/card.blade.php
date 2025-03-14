<x-sleek::tabs {{ $attributes }}>
@forwardSlots

<x-slot bind="$tabs">
    <x-bs::card>
        <x-slot:header>
            <ul class="nav nav-tabs card-header-tabs">
                @foreach($tabs->headers as $header)
                    <li class="nav-item">
                        <a {{ $header->attributes->class(['nav-link']) }}>{{ $header->label }}</a>
                    </li>
                @endforeach
            </ul>
        </x-slot:header>

        <x-slot:body {{ $tabs->body->attributes }}>
            {{ $tabs->body }}
        </x-slot:body>
    </x-bs::card>
</x-slot>
</x-sleek::tabs>
