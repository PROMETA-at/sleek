@props(['keyField' => 'tab', 'default' => null, 'clientSideNavigation' => false, 'htmx' => true])
@php
    use Illuminate\View\ComponentSlot;
    use Prometa\Sleek\Tabs\{Tab, TabCollection, TabsContext};

    $tabSlots = collect($__laravel_slots)
        ->filter(fn ($_, $key) => str_starts_with($key, 'tab'))
        ->mapWithKeys(fn ($x, $key) => [strtolower(substr($key, 3)) => $x]);

    $activeSlot = request($keyField, $default ?? $tabSlots->keys()->first());

    $tabsContext = new TabsContext($keyField);
    $tabs = new TabCollection(
        $tabSlots
            ->map(fn (ComponentSlot $slot, $key) => tap(
                new Tab(
                    $key,
                    $slot->toHtml(),
                    $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                    $slot->attributes->get('label', ''),
                    $key === $activeSlot,
                    $tabsContext,
                ),
                fn (Tab $tab) => $tab->link->withAttributes($htmx ? [
                    'hx-get' => $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                    'hx-target' => 'next .tab-content',
                    'hx-swap' => 'beforeend',
                ]: [])))
    );

    if (request()->header('HX-Request') && request()->has($keyField)) {
        $tabHtml = isset($fragment) && is_callable($fragment)
            ? $fragment($tabs->current())
            : $tabs->current()->withAttributes(['hx-swap-oob' => 'true']);
        $__env->registerFragment($activeSlot, $tabHtml);
        view()->selectFragment($activeSlot);
    }
@endphp

{{ is_callable($slot) ? $slot($tabs, $tabsContext) : $slot }}