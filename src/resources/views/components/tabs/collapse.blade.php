<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs" use="$attributes">
        @php
            if (!$attributes->has('id'))
                $attributes['id'] = uuid_create();
        @endphp
        <div {{ $attributes->class(['accordion', 'accordion-flush' => $attributes->has('flush')]) }}>
            @foreach ($tabs->headers as $header)
                <div class="accordion-item">
                    <h2 @class(['accorion-header']) id="heading-{{ $header->key }}">
                        <button type="button"
                                {{ $header->attributes
                                    ->except(['_', 'href', 'hx-target', 'data-bs-target'])
                                    ->merge(['hx-target' => 'next .accordion-body'])
                                    ->class(['accordion-button', 'collapsed' => !$header->active])
                                }}
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $header->key }}"
                                aria-expanded="{{ $header->active ? 'true' : 'false' }}"
                                aria-controls="collapse-{{ $header->key }}"
                        >
                            {{ $header->label }}
                        </button>
                    </h2>
                    <div id="collapse-{{ $header->key }}"
                         @class(['accordion-collapse', 'collapse', 'show' => $header->active])
                         aria-labelledby="heading-{{ $header->key }}"
                         data-bs-parent="#{{ $attributes->get('id') }}"
                    >
                        <div class="accordion-body">
                            <div id="tab-{{ $header->key }}">
                                @if ($header->active)
                                    {{ $header->content }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-sleek::tabs>
