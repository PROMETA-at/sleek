@ensureSlotFor($nav, true)
@ensureSlotFor($content, true)

<x-sleek::tabs {{ $attributes }}>
@forwardSlots

<x-slot bind="$tabs" use="$attributes, $nav, $content">
    <x-bs::card hx-on::after-settle="this.querySelector(`a.nav-link#${event.detail.elt.id}-link`)?.tab.show()" {{ $attributes }}>
        <x-slot:header>
            <x-apply :hoc="$nav" base="bs::nav" class="nav-tabs card-header-tabs">
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
        </x-slot:header>

        @php($attributes = $content->attributes)
        <x-slot:body {{ $attributes->class(['tab-content']) }}>
            <x-wrap-with :component="$content">
                @foreach($tabs as $tab)
                  {{
                      $tab->withAttributes(fn ($a) => $a
                          ->class(['tab-pane', 'active' => $tab->active]))
                  }}
                @endforeach
            </x-wrap-with>
        </x-slot:body>
    </x-bs::card>
</x-slot>
</x-sleek::tabs>
