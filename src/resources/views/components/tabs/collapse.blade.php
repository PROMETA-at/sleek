@props(['flush' => false])

<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs" use="$flush, $attributes">
        @php
            if (!$attributes->has('id'))
                $attributes['id'] = uuid_create();
        @endphp
        <div {{ $attributes->class(['accordion', 'accordion-flush' => $flush]) }}>
            @foreach($tabs as $tab)
                <div class="accordion-item">
                    <h2 @class(['accordion-header']) id="heading-{{ $tab->id }}">
                        <button type="button"
                                {{ $tab->link->attributes
                                    ->except(['href', 'hx-target'])
                                    ->merge(['hx-target' => 'next .accordion-body'])
                                    ->class(['accordion-button', 'collapsed' => !$tab->active])
                                }}
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ $tab->id }}"
                                aria-expanded="{{ $tab->active ? 'true' : 'false' }}"
                                aria-controls="collapse-{{ $tab->id }}"
                        >
                            {{ $tab->link->content }}
                        </button>
                    </h2>
                    <div id="collapse-{{ $tab->id }}"
                         @class(['accordion-collapse', 'collapse', 'show' => $tab->active])
                         aria-labelledby="heading-{{ $tab->id }}"
                         data-bs-parent="#{{ $attributes->get('id') }}"
                    >
                        {{ $tab->withAttributes(fn ($a) => $a->class(['accordion-body'])) }}
                    </div>
                </div>
            @endforeach
        </div>
    </x-slot>
</x-sleek::tabs>
