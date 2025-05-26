@props(['keyField' => 'tab', 'default' => null, 'clientSideNavigation' => false, 'htmx' => true])
@php
    use Illuminate\View\ComponentSlot;
    use function Prometa\Sleek\capture;
    use Prometa\Sleek\Tabs\{Tab, TabCollection, TabsContext};

    $tabSlots = collect($__laravel_slots)
        ->filter(fn ($_, $key) => str_starts_with($key, 'tab'))
        ->mapWithKeys(fn ($x, $key) => [Str::snake(substr($key, 3)) => $x]);

    $activeSlot = request($keyField, $default ?? $tabSlots->keys()->first());

    $tabsContext = new TabsContext($keyField);
    $tabs = new TabCollection(
        $tabSlots
            ->map(fn (ComponentSlot $slot, $key) => tap(
                new Tab(
                    $key,
                    $slot->toHtml(),
                    $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                    $__laravel_slots["label" . Str::studly($key)] ?? $slot->attributes->get('label', ''),
                    $key === $activeSlot,
                    $tabsContext,
                ),
                fn (Tab $tab) => $tab->link->withAttributes($htmx ? [
                    'hx-get' => $slot->attributes->get('href', request()->urlWithQuery([$keyField => $key])),
                    'hx-target' => 'next .tab-content',
                    'hx-swap' => 'beforeend',
                    'hx-push-url' => 'true',
                ]: [])))
    );
@endphp

{{ is_callable($slot) ? $slot($tabs, $tabsContext) : $slot }}

@php
    /**
     * This section intentionally happens after executing the default slot above.
     *
     * The execution will likely append necessary properties to the tab and link elements.
     * This side effect is intentional; it allows the user to define properties in the normal
     * flow (without fragment capturing) without having to worry about whether a fragment or the full
     * component will be rendered.
     */
    if (request()->header('HX-Request') && request()->has($keyField)) {
        $tabs->current()->withAttributes(['hx-swap-oob' => 'true']);

        $tabHtml = isset($fragment) && is_callable($fragment)
            ? capture(fn () => $fragment($tabs->current()))
            : $tabs->current()->toHtml();

        $__env->registerFragment($activeSlot, $tabHtml);
        view()->selectFragment($activeSlot);
    }
@endphp