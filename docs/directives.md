# Blade Directives

Sleek adds a handful of custom Blade directives that solve real, recurring pain points in component
authoring. If you've ever wrestled with output buffering to capture markup into a variable, written
tedious `isset` checks for optional slots, or tried to build a wrapper component that transparently
passes slots through -- these are for you.

## @capture / @into

Say you're building a page layout and you need to prepare a chunk of markup *before* passing it into
a component slot. Normally you'd reach for string concatenation or awkward inline expressions. With
`@capture` and `@into`, you just write normal Blade:

```blade
@capture
    <h1>{{ $project->name }}</h1>
    <span class="badge">{{ $project->status }}</span>
@into($header)

<x-page-layout>
    <x-slot:header>{{ $header }}</x-slot:header>

    <p>The rest of your page content goes here.</p>
</x-page-layout>
```

What happened there? Everything between `@capture` and `@into($header)` was rendered and stored in
`$header` as a `ComponentSlot` instance. That's the key detail -- because it's a proper
`ComponentSlot`, you can drop it anywhere a slot is expected. Under the hood Sleek uses output
buffering, so you're free to use any Blade syntax inside the captured block: conditionals, loops,
component tags, the works.

This is especially handy when you need to build up slot content from computed data before handing it
off to a component, or when you want to reuse the same rendered block in multiple places on a page.

## @flags / @flag

Here's a common situation: you're writing a component -- let's say a modal -- that has several
boolean options. Should it show a close button? Should it use native `<dialog>` behavior? Should the
body scroll? You *could* define each as a regular prop, but then your consumers have to write
`:close="false"` every time they want to turn something off. What you really want is a clean,
expressive API like `noclose`.

That's exactly what `@flags` gives you:

```blade
{{-- In your modal component --}}
@flags(['close', 'native' => false, 'scroll'])
```

This single line sets up three boolean flags with automatic negated variants:

| Flag       | Default | Positive prop | Negative prop |
|------------|---------|---------------|---------------|
| `close`    | `true`  | `close`       | `noclose`     |
| `native`   | `false` | `native`      | `nonative`    |
| `scroll`   | `true`  | `scroll`      | `noscroll`    |

Notice the convention: a bare string like `'close'` defaults to **on**, while a key-value pair like
`'native' => false` defaults to **off**. Each flag automatically gets a `no`-prefixed counterpart,
so consumers can use whichever reads more naturally.

Inside your component template, use `@flag` and `@unlessFlag` for conditional rendering:

```blade
@flag('close')
    <button class="btn-close" aria-label="Close"></button>
@endflag

@unlessFlag('native')
    {{-- Custom Bootstrap modal wrapper --}}
    <div class="modal-backdrop"></div>
@endflag
```

And from the consumer side, both of these are equivalent -- use whichever reads better in context:

```blade
{{-- Turn off the close button --}}
<x-modal noclose />
<x-modal :close="false" />

{{-- Turn on native dialog behavior (it's off by default) --}}
<x-modal native />
<x-modal :native="true" />
```

The result is a component API that reads like prose: `<x-modal noclose noscroll>` tells you
everything at a glance.

## @forwardSlots

Wrapper components are a natural pattern -- you build a specialized version of a more general
component. The tricky part? Slots. If your inner component accepts `header`, `footer`, and `actions`
slots, your wrapper suddenly needs to know about all of them and pass each one through manually.
That's fragile, and it breaks every time the inner component adds a new slot.

`@forwardSlots` takes care of it in one line:

```blade
{{-- card-with-actions.blade.php (a wrapper around your base card component) --}}
<x-card {{ $attributes }}>
    @forwardSlots
    {{ $slot }}
</x-card>
```

Every named slot that was passed to your wrapper (except the default `$slot`) is forwarded to the
child component with its attributes intact. Your wrapper doesn't need to know or care what named
slots the inner component supports -- it just passes everything through. When the inner component
gains a new slot next month, your wrapper already handles it.

## @ensureSlotFor

If you've written more than a few Blade components, you've probably written this kind of guard:

```blade
@if(isset($header) && $header->isNotEmpty())
    <div class="card-header">{{ $header }}</div>
@endif
```

The `isset` check is there because `$header` might not exist at all if the consumer didn't provide
that slot. And if they passed a plain string instead of using `<x-slot:header>`, you can't call
`->isNotEmpty()` on it without things breaking. It's tedious and error-prone.

`@ensureSlotFor` normalizes all of that:

```blade
@ensureSlotFor($header)
@ensureSlotFor($footer, true)
```

After these lines, `$header` and `$footer` are guaranteed to be `ComponentSlot` instances, so you
can safely call slot methods and access attributes on them.

The two modes work like this:

- **Without the force flag** (`@ensureSlotFor($header)`): if `$header` is a string, it gets wrapped
  in a `ComponentSlot`. If it's already a `ComponentSlot`, nothing changes. If the variable doesn't
  exist at all, it stays undefined -- you still need an `isset` check.

- **With the force flag** (`@ensureSlotFor($footer, true)`): does everything above, *plus* creates
  an empty `ComponentSlot` if the variable doesn't exist. This is the "I never want to think about
  this slot being missing" mode.

With the force flag, your template code gets much cleaner:

```blade
@ensureSlotFor($header, true)
@ensureSlotFor($footer, true)

@if($header->isNotEmpty())
    <div class="card-header" {{ $header->attributes }}>{{ $header }}</div>
@endif

<div class="card-body">{{ $slot }}</div>

@if($footer->isNotEmpty())
    <div class="card-footer" {{ $footer->attributes }}>{{ $footer }}</div>
@endif
```

No `isset`, no type checks, just clean conditional rendering. The slot attributes are safely
accessible too, so you can forward them right onto your wrapper elements.

## Scoped Slots (`scopedSlots()`)

A normal Blade slot body runs *at the call site*, before your component ever sees it. That's usually fine, but it
means two things you can't fix from inside the component: an inactive tab's expensive query still runs, and a slot
you intend to call yourself (once per table row, say) has no way to receive arguments unless the consumer opts in
with `bind`. Sleek's tabs and entity-table solve both by registering their slots as *scoped* — the slot compiles
to a closure instead of eager output. The same mechanism is open to your own components:

```php
// In your service provider's boot(), inside callAfterResolving('blade.compiler', ...):
$bladeCompiler->scopedSlots('acme::accordion*', 'panel-*');
$bladeCompiler->scopedSlots('acme::data-grid', 'cell-*', params: '$value, $row');
```

That's it. Now any `panel-*` slot written inside an `<x-acme::accordion>` (or any preset matching
`acme::accordion*`) defers until your component invokes it, and any `cell-*` slot inside `<x-acme::data-grid>`
becomes a callable that receives `$value, $row`.

There are two modes, and the difference is whether your component hands the slot any arguments:

### Zero-argument mode

Leave `params` off and matching slots compile to argument-less closures. Nothing new appears in scope — the body
sees exactly the variables it would have seen as a plain slot — it just doesn't *run* until you render it. This is
the tabs case: the consumer writes an ordinary-looking slot and never learns it became lazy.

```blade
<x-acme::accordion>
    <x-slot:panel-billing label="Billing">
        {{ $account->invoiceHistory() }}  {{-- only runs when this panel is shown --}}
    </x-slot:panel-billing>
</x-acme::accordion>
```

Inside your component, invoke the slot when you want its output — Sleek's `CallableComponentSlot` renders with
`{{ $panelBilling }}` or `$panelBilling->toHtml()`. Adding a `bind` to a zero-argument slot is a **compile-time
error**: it could only ever fail at runtime, so we catch it early.

### Parameterized mode

Pass `params` and the consumer keeps writing `bind` explicitly — naming what they receive. This is the
entity-table case:

```blade
<x-acme::data-grid :rows="$orders" :columns="['total']">
    <x-slot:cell-total bind="$value, $row">
        <strong>{{ money($value) }}</strong> for {{ $row->customer }}
    </x-slot:cell-total>
</x-acme::data-grid>
```

Forget the `bind` and you get a **compile-time error** that suggests the exact attribute to write
(`bind="$value, $row"`) — the string you passed as `params`. Note the design rule at work here: the compiler
never invents variable names. In zero-argument mode it defers execution invisibly; in parameterized mode it makes
the consumer name what they receive. It will never silently inject a `$value` you didn't declare.

In both modes the slot body has full access to the surrounding template scope — variables defined before the slot
are simply available inside it, and so is `$loop` inside a `@foreach`.

### Two things to know

- **Register at boot, before anything compiles.** Registrations live on the compiler, and slots consult them the
  moment a template compiles. The `callAfterResolving('blade.compiler', ...)` hook is the right place.
- **Changing registrations needs `php artisan view:clear`.** Because the compiled output of a template depends on
  what's registered, adding, removing, or editing a `scopedSlots()` call means existing compiled views are stale.
  Clear them and they recompile. This is the same caveat as any Blade compiler extension.

Matching is graceful: a slot the registry doesn't recognize — a dynamically-named slot, a slot inside
`<x-dynamic-component>`, anything the compiler can't attribute to a registered component — simply compiles the way
it always did. You never get a *new* failure mode, only the scoped behavior where it applies.
