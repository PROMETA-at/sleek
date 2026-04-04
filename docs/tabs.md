# Tabs

Sleek's tab components let you drop in fully-functional tabbed navigation with almost zero effort. Just define your
tab slots and Sleek takes care of the rest — state management, URL handling, and if you're using HTMX, even AJAX
loading with no wiring on your part.

Here's what a basic tabbed interface looks like:

```blade
<x-sleek::tabs.pills>
    <x-slot:tab-overview label="Overview">
        Overview content here...
    </x-slot:tab-overview>
    <x-slot:tab-settings label="Settings">
        Settings content here...
    </x-slot:tab-settings>
</x-sleek::tabs.pills>
```

That's it. The above declaration automagically sets up a few things:

- The tab keys are derived from the slot names (minus the `tab-` prefix), so `tab-overview` becomes the `overview` tab.
- The active tab is read from the `?tab=` query string parameter. If there's no parameter, the first tab is shown.
- Tab buttons are rendered as regular `<a>` links — without JavaScript they simply reload the page with the right query parameter.
- **With HTMX installed**, tabs load via AJAX automatically. The component wires up all the necessary HTMX attributes and handles fragment rendering internally. No `hx-get`, no `hx-target`, no `hx-swap` — it just works.

Lets break it down.

## Tab Identification

Each tab gets its key from the slot name. The `tab-` prefix is stripped, so `tab-overview` becomes `overview`,
`tab-settings` becomes `settings`, and so on. This key is what appears in the URL query string and what you
reference when setting a default tab.

## Active Tab Resolution

By default, the component looks for a query parameter called `tab` to decide which tab is active. So visiting
`/your-page?tab=settings` will activate the settings tab. If the parameter is missing entirely, the first tab
wins.

You can customize the query parameter name with `key-field`, which is handy when you have multiple tab groups on
the same page (or you just want a more descriptive URL):

```blade
<x-sleek::tabs.pills key-field="section">
    <x-slot:tab-overview label="Overview">...</x-slot:tab-overview>
    <x-slot:tab-settings label="Settings">...</x-slot:tab-settings>
</x-sleek::tabs.pills>
```

Now the URL reads `?section=settings` instead of `?tab=settings`.

If you want a specific tab to be active when no query parameter is present (instead of always defaulting to the
first one), you can set `default`:

```blade
<x-sleek::tabs.pills default="settings">
    <x-slot:tab-overview label="Overview">...</x-slot:tab-overview>
    <x-slot:tab-settings label="Settings">...</x-slot:tab-settings>
</x-sleek::tabs.pills>
```

## HTMX Integration

This is where things get really nice. If you have HTMX on your page, clicking a tab won't trigger a full page
reload — the component automagically adds all the HTMX attributes needed for partial page updates. It handles
the fragment rendering internally too, so only the tab content gets swapped in over AJAX.

You don't need to do anything to opt in. If HTMX is present, it just works.

> **Caveat:** Because the tabs component overrides Laravel's fragment resolution internally, you cannot nest a
> tabs component inside another fragment. Keep that in mind if you're composing complex HTMX-driven layouts.

## Styled Presets

Sleek ships with four styled presets out of the box, so you can pick the look that fits your UI without writing
any custom markup:

### Pills

Horizontal Bootstrap pills — great for standard top-level navigation between content sections:

```blade
<x-sleek::tabs.pills>
    <x-slot:tab-overview label="Overview">...</x-slot:tab-overview>
    <x-slot:tab-settings label="Settings">...</x-slot:tab-settings>
</x-sleek::tabs.pills>
```

### Vertical

Vertical pills on the left with content on the right — useful for settings pages or sidebar-style navigation:

```blade
<x-sleek::tabs.vertical>
    <x-slot:tab-profile label="Profile">...</x-slot:tab-profile>
    <x-slot:tab-security label="Security">...</x-slot:tab-security>
</x-sleek::tabs.vertical>
```

### Card

A Bootstrap card with the tab navigation sitting in the card header:

```blade
<x-sleek::tabs.card>
    <x-slot:tab-details label="Details">...</x-slot:tab-details>
    <x-slot:tab-history label="History">...</x-slot:tab-history>
</x-sleek::tabs.card>
```

### Collapse (Accordion)

Renders as a Bootstrap accordion — each tab becomes a collapsible section:

```blade
<x-sleek::tabs.collapse>
    <x-slot:tab-faq label="FAQ">...</x-slot:tab-faq>
    <x-slot:tab-contact label="Contact">...</x-slot:tab-contact>
</x-sleek::tabs.collapse>
```

If you want a borderless accordion that sits flush against its container (no outer borders or rounded corners),
pass the `flush` attribute:

```blade
<x-sleek::tabs.collapse flush>
    <x-slot:tab-faq label="FAQ">...</x-slot:tab-faq>
    <x-slot:tab-contact label="Contact">...</x-slot:tab-contact>
</x-sleek::tabs.collapse>
```

## Headless Component

When none of the presets fit your design, you can use the base `sleek::tabs` component and bring your own markup.
The component still handles all the tab state logic — you just control how it looks:

```blade
<x-sleek::tabs>
    <x-slot bind="$tabs">
        <ul>
            @foreach($tabs as $tab)
            <li>
                {{ $tab->link->withAttributes(fn ($a) => $a->class(['my-tab-link'])) }}
            </li>
            @endforeach
        </ul>

        <div>
            @foreach($tabs as $tab)
                {{ $tab->withAttributes(fn ($a) => $a->class(['my-tab-pane'])) }}
            @endforeach
        </div>
    </x-slot>

    <x-slot:tab-one label="First">Tab one content</x-slot:tab-one>
    <x-slot:tab-two label="Second">Tab two content</x-slot:tab-two>
</x-sleek::tabs>
```

Each `$tab` object gives you everything you need to build your own tab UI:

- `$tab->link` — an `Htmlable` representing the tab link, with its own `ComponentAttributeBag`
- `$tab->content` — the tab's rendered HTML content (only populated for the active tab)
- `$tab->active` — a boolean indicating whether this tab is currently active
- `$tab->id` and `$tab->key` — identifiers for the tab

Both `$tab` and `$tab->link` support `withAttributes()`, so you can fluently add classes, data attributes, or
anything else you need like the following:

```blade
{{ $tab->link->withAttributes(fn ($a) => $a->class(['active' => $tab->active])) }}
{{ $tab->withAttributes(fn ($a) => $a->class(['tab-pane', 'show' => $tab->active])) }}
```

> **Note:** The default slot uses a callable slot (via `bind`) to ensure it evaluates after all tab slots have
> been processed. This is what makes the `$tabs` variable available — without `bind`, the slot would render
> before the tab slots are collected.
