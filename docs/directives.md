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
