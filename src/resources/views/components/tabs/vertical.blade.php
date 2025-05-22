@ensureSlotFor($nav, true)
@ensureSlotFor($content, true)

<x-sleek::tabs {{ $attributes }}>
    @forwardSlots

    <x-slot bind="$tabs" use="$nav, $content">
        <div class="d-flex align-items-start" hx-on::after-settle="this.querySelector(`a.nav-link#${event.detail.elt.id}-link`)?.tab.show()">
            <x-apply :hoc="$nav" base="bs::nav.pills" orientation="vertical">
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
            </x-apply>

            <x-apply :hoc="$content" base="bs::tabs.content">
                @foreach($tabs as $tab)
                    {{
                        $tab->withAttributes(fn ($a) => $a
                            ->class(['tab-pane', 'active' => $tab->active]))
                    }}
                @endforeach
            </x-apply>
        </div>
    </x-slot>
</x-sleek::tabs>
