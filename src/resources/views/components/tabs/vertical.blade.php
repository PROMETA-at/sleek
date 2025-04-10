<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs">
        <div class="d-flex align-items-start" hx-on::after-settle="this.querySelector(`a.nav-link#${event.detail.elt.id}-link`)?.tab.show()">
            <x-bs::nav.pills orientation="vertical">
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
            </x-bs::nav.pills>

            <x-bs::tabs.content>
                @foreach($tabs as $tab)
                    {{
                        $tab->withAttributes(fn ($a) => $a
                            ->class(['tab-pane', 'active' => $tab->active]))
                    }}
                @endforeach
            </x-bs::tabs.content>
        </div>
    </x-slot>
</x-sleek::tabs>
