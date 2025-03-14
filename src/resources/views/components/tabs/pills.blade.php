<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs">
        <ul class="nav nav-pills">
            @foreach($tabs->headers as $header)
                <li class="nav-item">
                    <a {{ $header->attributes->class(['nav-link']) }}>{{ $header->label }}</a>
                </li>
            @endforeach
        </ul>

        <div {{ $tabs->body->attributes }}>
            {{ $tabs->body }}
        </div>
    </x-slot>
</x-sleek::tabs>
