# SPEC (DRAFT): Make tab content rendering lazy

> **Status:** ✅ Implemented via
> [`2026-07-07-scoped-slot-registry.md`](2026-07-07-scoped-slot-registry.md). This document remains as the
> problem framing and option-space history. The option space below is obsolete: approaches A/B cannot work (slot
> bodies execute at capture time, before the component view runs), so the fix lives in the compiler — a
> scoped-slot registry that compiles `tab-*` slots to closures. Inactive tab bodies no longer execute.
>
> **Related:** [`2026-06-19-deferred-content-component.md`](2026-06-19-deferred-content-component.md)

## Problem

Tab slot bodies are **executed for every tab on every render**, even
though only the active tab's HTML is kept. The HTMX integration makes the
tabs *look* lazy — inactive panes render as empty `<div>`s and their
content is fetched on click — but the server still does the full work of
rendering every tab's body up front. For tabs whose body is expensive
(a heavy query, an aggregate, an external API call), that defeats the
purpose: you pay for tabs the user never opens, on every page load and on
every tab switch.

This bit us concretely: a "Statistics" tab ran its aggregate queries on
every load of the offers index, even when the user was looking at the
default "Videos" tab. We worked around it consumer-side by replacing the
tab body with an htmx-deferred placeholder — but the tab component itself
should not require that dance.

Notably, `docs/tabs.md` already *documents the intended behavior*:

> `$tab->content` — the tab's rendered HTML content (**only populated for
> the active tab**)

So the contract is already "lazy." The implementation just doesn't honor
it — `content` is *computed* for every tab and then discarded for the
inactive ones. This is closer to a bug against documented behavior than a
new feature.

## Current architecture

`src/resources/views/components/tabs/index.blade.php` builds the tab
collection by eagerly rendering each slot at construction:

```php
$tabs = new TabCollection(
    $tabSlots->map(fn (ComponentSlot $slot, $key) => tap(
        new Tab(
            $key,
            $slot->toHtml(),   // ← every slot rendered here, active or not
            ...
            $key === $activeSlot,
            $tabsContext,
        ),
        ...
    ))
);
```

`Tab::toHtml()` then keeps the content only when active:

```php
public function toHtml()
{
    return $this->active ? $this->withContainer($this->content) : $this->empty();
}
```

So: **render everything, output only the active one.** The `$content`
constructor parameter is typed `string`, i.e. already-rendered HTML — the
laziness can't live in `Tab`, it has to live in *how/whether `toHtml()` is
called on the slot*.

### Empirical confirmation

Rendering a `tabs.card` whose **inactive** tab body calls a method on
`null` throws `Call to a member function …() on null` — proving the
inactive slot body executes. (Tested against vendored v0.0.157, which is
byte-identical to current `main`.)

## Why it matters

- Expensive tabs tax every page view and every tab switch, regardless of
  what's visible.
- It silently penalizes the *idiomatic* usage (put your content in the
  slot) and rewards a consumer-side workaround (deferred placeholder),
  which is backwards.
- It contradicts the documented `$tab->content` contract.

## Constraints / things any fix must preserve

1. **Active tab still renders inline** on first load (no flash / no extra
   request for the tab you're already looking at).
2. **HTMX fragment flow** keeps working: clicking an inactive tab issues
   `hx-get="?tab=key"`, the controller re-renders, and the component
   returns just that tab's fragment (`registerFragment` / `selectFragment`
   + `hx-swap-oob`). Under lazy rendering the requested tab *is* active on
   that request, so its body runs — which is exactly what we want.
3. **No-htmx degradation**: without htmx, tab links are full-page reloads
   with `?tab=key`; the now-active tab renders. Lazy rendering is fine
   here too (only the active one ever needed rendering).
4. **Headless API** (`$tab->content`, `$tab->active`, the `bind` default
   slot) stays source-compatible. `content` for an inactive tab is already
   documented as unpopulated.
5. **Side-effect-free expectation**: today a slot body *can* rely on being
   executed (e.g. registering something as a side effect). Making it lazy
   changes that. Probably acceptable — slots shouldn't have render side
   effects — but call it out as a (minor) breaking-behavior risk.

## Candidate approaches (to weigh)

### A. Defer slot rendering behind a closure

Pass the slot (or a thunk) into `Tab` instead of a pre-rendered string;
render lazily inside `toHtml()` only when `active` (or when explicitly
asked, e.g. by the fragment path).

- `Tab::$content` becomes computed-on-demand (memoized). Constructor takes
  `ComponentSlot|Closure` instead of `string`.
- Pro: minimal surface, directly fixes the eager `$slot->toHtml()`.
- Con: `$tab->content` in the headless API changes from "string, present
  only when active" to "string, materialized on access" — keep it a
  property accessor returning `''` when inactive to preserve the contract.
- Watch: the bottom-of-file fragment block calls `$tabs->current()->toHtml()`
  — `current()` is the active tab, so its closure runs there. Fine.

### B. Only call `toHtml()` on the active slot at construction

Keep `Tab` taking a string, but in `index.blade.php` compute
`$slot->toHtml()` only for `$key === $activeSlot`, pass `''` otherwise.

- Pro: smallest diff; `Tab` unchanged.
- Con: pushes the active-check into the blade; the link-attribute `tap()`
  still needs to run for every tab (that part is cheap and must stay).

### C. Opt-in laziness

Add an attribute (e.g. `lazy` on the tab slot or the tabs component) that
selects eager vs. lazy, defaulting eager for backwards safety.

- Pro: zero risk to existing consumers.
- Con: laziness should arguably be the default (it matches the docs and
  the HTMX promise); an opt-in flag is a smell for "the default is wrong."
  Possibly a transitional step toward making it default.

## Open questions

1. Lazy by **default**, or behind a flag (approach C) for a release or two?
   Given the documented contract already says "only the active tab," lazy
   default seems defensible — but it's a behavior change for anyone
   (accidentally) relying on inactive slot execution.
2. Does any current consumer rely on inactive slot bodies executing for
   side effects? (Audit before flipping the default.)
3. Interaction with `clientSideNavigation` / non-htmx: confirm the active
   tab is always the one rendered in every path (full page, htmx fragment,
   no-js reload).
4. Should this subsume the deferred-component tab use case? If tabs are
   lazy, expensive tab content needs no `x-sleek::deferred` wrapper — see
   the related PRD. Decide whether tabs should *internally* reuse a
   "load on show" primitive or keep the request-on-click model they have.
5. Memoization: ensure a tab rendered both inline (active) and via the
   fragment path within one request isn't rendered twice.

## Rough test ideas

- Inactive tab body with an observable side effect (increment a counter /
  throw) ⇒ assert it does **not** run when another tab is active.
- Active tab body **does** render inline on first load.
- HTMX fragment request for a previously-inactive tab returns that tab's
  fully-rendered content.
- No-htmx full-page reload with `?tab=key` renders that tab.
- Headless `$tab->content` still empty for inactive, populated for active.
