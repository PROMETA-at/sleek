<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs">
        <div class="d-flex align-items-start">
            <ul class="nav flex-column nav-pills me-3" role="tablist" aria-orientation="vertical">
                @foreach($tabs->headers as $header)
                    <li class="nav-item">
                        <button {{ $header->attributes->class(['nav-link']) }}>{{ $header->label }}</button>
                    </li>
                @endforeach
            </ul>
            <div {{ $tabs->body->attributes }}>
                {{ $tabs->body }}
            </div>
        </div>
    </x-slot>
</x-sleek::tabs>
