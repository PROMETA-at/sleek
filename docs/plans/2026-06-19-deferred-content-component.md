# PRD (DRAFT): `x-sleek::deferred` — htmx-deferred content

> **Status:** Draft / proposal. Not scheduled. Captured from a consumer
> implementation in `online-systembrett` (admin panel) that we'd like to
> promote into Sleek "in some form." Hash out the API before building.
>
> **Related:** [`2026-06-19-lazy-tab-content-rendering.md`](2026-06-19-lazy-tab-content-rendering.md)
> — the two overlap; see *Relationship to lazy tabs* below.

## Summary

A tiny anonymous component that renders a placeholder (spinner) which
htmx-fetches a URL and swaps itself out for the response. It lets a page
paint immediately and stream in expensive sections afterwards, with zero
JS wiring on the consumer's part — the same "it just works if htmx is
present" ergonomics as `tabs`.

```blade
<x-sleek::deferred :src="route('dashboard.data')" />
```

## Motivation

Server-rendered pages that aggregate expensive data (dashboards, revenue
panels, anything backed by heavy queries) pay the full cost before the
first byte reaches the browser. The page feels slow even though most of
it — the shell, the nav, the controls — is cheap.

The established fix is progressive loading: render the shell now, fetch
the heavy parts over htmx. But wiring that by hand means, every time:

- a placeholder element with `hx-get` / `hx-trigger` / `hx-swap`,
- a spinner,
- a second controller action + route returning the partial,
- remembering the right trigger (`load` vs. visibility).

The first three are pure boilerplate. Sleek already owns this kind of
"aggressively default the boilerplate, keep it customizable" surface.

## Prior art (the consumer implementation)

The version currently living in `online-systembrett`:

```blade
@props(['src', 'trigger' => 'load'])

<div
    {{ $attributes }}
    hx-get="{{ $src }}"
    hx-trigger="{{ $trigger }}"
    hx-swap="outerHTML"
>
    <div class="d-flex justify-content-center align-items-center text-muted py-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">{{ __('common.loading') }}</span>
        </div>
    </div>
</div>
```

Used in two shapes:

1. **Dashboard** — shell renders instantly; data streams in after load:
   ```blade
   <x-deferred :src="route('dashboard.data', ['period' => $period->key])" />
   ```
2. **Statistics tab** — content loads only when the tab is first shown:
   ```blade
   <x-deferred :src="route('video-offers.statistics')" trigger="intersect once" />
   ```

It works well in practice. Charts (custom elements) and hyperscript
`_="init …"` tooltips both initialize correctly after the swap, because
custom-element upgrade and the hyperscript MutationObserver fire on
htmx-inserted nodes.

## Proposed API

| Prop / attr | Default | Purpose |
|---|---|---|
| `src` (required) | — | URL to fetch. |
| `trigger` | `load` | htmx trigger. `load` = fetch right after page load. `intersect once` = fetch when first scrolled/toggled into view. |
| `swap` | `outerHTML` | htmx swap strategy for replacing the placeholder. |
| *(forwarded attrs)* | — | Merged onto the root element (`{{ $attributes }}`), so consumers can add `class`, `id`, `hx-*` overrides, etc. |
| `$slot` (optional) | spinner | Custom placeholder markup. Empty slot → default centered spinner. |

Rendered as `<x-sleek::deferred>`, living at
`src/resources/views/components/deferred.blade.php`.

### Default placeholder

Default to the centered Bootstrap spinner with a translated visually-hidden
label (Sleek already ships a translation pipeline; reuse it rather than
the consumer's `common.loading`). Allow override via the slot:

```blade
<x-sleek::deferred :src="route('reports.heavy')">
    <x-slot:placeholder>
        <div class="skeleton-block" aria-busy="true">…</div>
    </x-slot:placeholder>
</x-sleek::deferred>
```

## Use cases

- Below-the-fold or expensive panels on an otherwise cheap page.
- Dashboards / analytics where the shell + filters are cheap and the
  aggregates are not.
- Anything currently reaching for "render a spinner, fetch the rest."

## Open questions (for hashing out)

1. **Trigger ergonomics.** `trigger="load"` vs `trigger="intersect once"`
   is the 90% split. Worth first-class boolean-ish aliases (e.g.
   `when-visible`) instead of leaking raw htmx trigger syntax? Keeps the
   "you don't write htmx" promise of `tabs`.
2. **No-htmx fallback.** `tabs` degrades to full-page links without htmx.
   `deferred` has no obvious no-JS story — the content simply never loads.
   Options: render the content inline when htmx is absent (defeats the
   purpose but is safe), or document it as htmx-required. Decide.
3. **Error / empty handling.** What shows if the fetch 500s or returns
   empty? htmx default leaves the spinner spinning forever. A minimal
   `hx-on::response-error` → error state may be worth baking in.
4. **Translation key.** Reuse Sleek's translation namespace for the
   loading label rather than assuming a consumer `common.loading`.
5. **Naming.** `deferred`? `lazy`? `async`? `defer`? Pick one.

## Relationship to lazy tabs

If the [lazy-tab spec](2026-06-19-lazy-tab-content-rendering.md) lands,
the *tab* use case for `deferred` (#2 above) largely dissolves — a lazy
tab already fetches its own content over htmx only when shown, so you'd
just put the expensive content directly in the tab slot and let the tab
mechanism defer it. `deferred` would then be for **non-tab** progressive
loading (the dashboard case) and for explicit second-level deferral.

Decide whether the two should share machinery (e.g. tabs internally
expressing "load on show" via the same primitive) or stay independent.

## Out of scope

- Polling / auto-refresh (`hx-trigger="every Ns"`) — consumers can pass
  `trigger` directly if they really want it; not a documented feature.
- Caching of fetched fragments.
- Skeleton-screen presets beyond "bring your own slot."
