@props(['keyField' => 'tab', 'default' => null, 'clientSideNavigation' => false])
@php
    use Illuminate\View\ComponentAttributeBag;
    use Illuminate\View\ComponentSlot;

    $tabSlots = collect($__laravel_slots)
        ->filter(fn ($_, $key) => str_starts_with($key, 'tab'))
        ->mapWithKeys(fn ($x, $key) => [strtolower(substr($key, 3)) => $x]);

    $renderedSlotKey = request($keyField, $default ?? $tabSlots->keys()->first());
    $renderedSlot = $tabSlots->get($renderedSlotKey, null);
    if (request()->header('HX-Request') && request()->has($keyField)) {
        $tabHtml = "<div class=\"tab-pane active show\" hx-swap-oob=\"true\" id=\"{$keyField}-{$renderedSlotKey}\">{$renderedSlot}</div>";
        $__env->registerFragment($renderedSlotKey, $tabHtml);
        view()->selectFragment($renderedSlotKey);
    }

    $tabs = new stdClass;
    $tabs->headers = collect(
        $tabSlots
            ->map(fn ($slot, $key) => (object) [
                'key' => $key,
                'label' => $slot->attributes->get('label', ''),
                'active' => $slot === $renderedSlot,
                'attributes' => $slot->attributes->except(['label'])
                    ->class(['active' => $slot === $renderedSlot])
                    ->merge([
                        'href' => $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                        'hx-get' => $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                        'hx-target' => 'next .tab-content',
                        'hx-swap' => 'beforeend',
                        '_' => <<<EOF
                            init
                                make a bootstrap.Tab from me
                                set :tab to it
                            on htmx:afterSettle(elt) from next .tab-content
                                if elt matches #{$keyField}-{$key} then
                                    call :tab.show()
                        EOF,
                        'data-bs-target' => "#{$keyField}-{$key}"
                    ]),
                'content' => $slot,
            ])
            ->values()
    );

    $tabsHtml = $tabSlots->map(fn ($slot, $key) =>
        "<div class=\"tab-pane ".($key === $renderedSlotKey ? 'active' : '')."\" id=\"{$keyField}-{$key}\">".($key === $renderedSlotKey ? $slot : '').'</div>'
    )->join('');
    $tabs->body = new ComponentSlot(
        $tabsHtml,
        with(new ComponentAttributeBag())
            ->class(['tab-content'])
            ->getAttributes(),
    );
@endphp

{{ is_callable($slot) ? $slot($tabs) : $slot }}