<x-sleek::tabs {{ $attributes }}>
@forwardSlots

<x-slot bind="$tabs">
    <x-bs::card hx-on::after-settle="this.querySelector(`a.nav-link#${event.detail.elt.id}-link`)?.tab.show()">
        <x-slot:header>
            <ul class="nav nav-tabs card-header-tabs">
                @foreach($tabs as $tab)
                    <x-bs::nav.item>
                        {{
                            $tab->link->withAttributes(fn ($a) => $a
                                ->class(['nav-link', 'active' => $tab->active])
                                ->merge([
                                    'id' => "{$tab->id}-link",
                                    'data-bs-target' => "#{$tab->id}",
                                    'hx-on:init' => 'this.tab = new bootstrap.Tab(this)',
                                ]))
                        }}
                    </x-bs::nav.item>
                @endforeach
            </ul>
        </x-slot:header>

        <x-slot:body class="tab-content">
            @foreach($tabs as $tab)
                {{
                    $tab->withAttributes(fn ($a) => $a
                        ->class(['tab-pane', 'active' => $tab->active]))
                }}
            @endforeach
        </x-slot:body>
    </x-bs::card>
</x-slot>
</x-sleek::tabs>
