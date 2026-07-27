# PRD — Sleek limitations reported from consumer projects

**Date:** 2026-07-25
**Status:** Proposed
**Source:** Field feedback from a consuming admin panel (publications/tenants domain)

## Context

Eight limitations were reported by a team building an admin panel on Sleek. Each was
traced to current package code before writing this document; the "Today" sections below
are grounded in `file:line` references, not in the reporter's description.

Two findings worth surfacing up front:

- **Item 4 (entity-table scoped slots) is a documentation bug, not a capability gap.** The
  `use` attribute on a scoped slot mirrors the `use` keyword of a PHP closure and compiles
  to exactly that (`src/Blade/BladeCompiler.php:78-82`). It solves the reported problem
  today and always has — it is simply undocumented outside a plan file. The in-flight
  scoped-slot-registry work makes it *unnecessary* for this case, but the report itself
  was caused by docs, not code.
- Three items turned up **adjacent defects the reporter did not notice**: `env('APP_NAME')`
  silently renders empty under `config:cache`, `EntityTable::$responsive` is declared but
  never forwarded, and the language switcher is nested inside the auth block so
  `Sleek::authentication(false)` also removes it.

Sleek's design principle — *aggressively default to the most likely use case, while
leaving every part explicitly customizable* — holds up on the "default" half. Every one
of these reports is a failure of the **second** half: either a sensible default with no
escape hatch (items 1, 2, 3, 5, 6, 7, 8), or — in item 4 — an escape hatch that exists but
is undocumented, which from the consumer's side is indistinguishable from absent. The
changes below add or surface escape hatches without moving the defaults.

That distinction is worth holding onto beyond this document. When behaviour is driven by
convention rather than explicit props, the documentation *is* the API surface. An
undocumented escape hatch is not a partial success; it is a defect of the same severity as
a missing one, and item 4 cost the consuming team schema changes to work around.

## Cross-cutting themes

Three structural issues recur across the individual items and should be treated as
shared workstreams rather than repeated per item:

**T1 — Inline `<style>` blocks instead of Sass.** Responsive table CSS, sidebar layout,
and pagination rules live in inline `<style>` blocks inside Blade views
(`entity-table.blade.php:58-110`, `navbar.blade.php:123,143`, `page/index.blade.php:32`,
`pagination.blade.php:105-127`). They are unreachable from a consumer's Sass, unoverridable
without `!important`, hardcode pixel values, and in the entity-table case are emitted once
per component instance with no `@once`. Items 7 and 8 both require moving these into
`src/resources/sass/`.

**T2 — `env()` used outside config.** `env('APP_NAME')` at `document.blade.php:7` and
`navbar.blade.php:6,8`. `config('app.name')` appears nowhere in the package. After
`php artisan config:cache` — i.e. in every correctly deployed production app — `env()`
returns `null` and both the document title and navbar brand render empty. This is a
standalone bug, not just a design limitation, and blocks item 3.

**T3 — No `config/sleek.php`.** All configuration is imperative through the `Sleek`
facade into a request-scoped `SleekPageState` singleton
(`SleekServiceProvider.php:137`). That is the right model for per-page state (titles,
menus, alerts) and should stay. But several items below need *install-time, per-app*
settings that do not vary per request — breakpoints, whether to register the lang route,
which guard to use. These do not belong in a request-scoped builder.

**Decision:** introduce a published `config/sleek.php` for install-time settings only,
and keep `SleekPageState` for per-request presentation state. The rule of thumb: if the
value could differ between two pages of the same app, it belongs in `SleekPageState`; if
it is fixed for the deployment, it belongs in config.

---

## 1. Form-field labels ignore a supplied `id`

**Report:** "Form-field labels derive `for` from the field name rather than a supplied
custom ID. Repeated forms therefore cannot get fully correct label association through
the straightforward API."

### Today

`FormField.php:53` defaults `$this->id ??= $this->name` — but only *after* rewriting
dotted names into bracket notation (`:51`), so the default id for `settings.theme` is
`settings[theme]`.

The wrapper never sees `$id`. `form-field/wrapper.blade.php:1` declares
`@aware(['name', 'label', 'type'])`, and line 11 emits:

```blade
for="{{ $name ?? $label->attributes->get('for') ?? '' }}"
```

`$name` is always non-null by this point, so the two fallbacks are dead code — a
user-supplied `<x-slot:label for="…">` cannot win either. Result:
`<x-sleek::form-field name="foo" id="bar"/>` renders `<label for="foo">` against
`<input id="bar" name="foo">`. Nothing is associated.

Two further inconsistencies in the same family:

- `form-field/button-group.blade.php:9,13` builds ids as `{{ $name }}-{{ $value }}` and
  ignores `$id` entirely — a custom id is silently dropped.
- `form-field/radio-group.blade.php:5` correctly derives per-option ids from `$id`, but
  the outer wrapper label still emits `for="{{ $name }}"`, which matches no element in
  the document.
- `form-field/checkbox.blade.php` is the one type that is already correct, because it
  bypasses the wrapper and delegates to `x-bs::form-check`, which pairs `for` with the
  real id (`bootstrap/components/form-check.blade.php:24`).

### Why it matters

Repeated forms (the same entity form rendered once per row, or two forms on one page)
produce duplicate ids and mismatched labels. Clicking a label focuses the wrong control
or nothing. This is an accessibility defect, not only a cosmetic one, and there is no
workaround through the public API short of replacing the wrapper view.

### Proposed change

1. Add `id` to the wrapper's `@aware` list and derive `for` from it:
   `for="{{ $label->attributes->get('for') ?? $id }}"` — note the order is inverted from
   today, so an explicit `for` on the label slot wins, then the resolved id.
2. Forward `$id` through `button-group` so `{{ $id }}-{{ $value }}` is the option id,
   falling back to the name-derived form only when `$id` is unset.
3. For `radio-group`, drop the `for` on the group wrapper entirely and emit a
   `<fieldset>`/`<legend>` instead — a group label pointing at one arbitrary radio is
   wrong markup regardless of which id it names. (Semantic HTML over divs; this is the
   platform-native answer.)
4. Move the `$this->id ??= $this->name` default to **before** the bracket rewrite, so
   the default id is `settings-theme` rather than `settings[theme]`. Bracketed ids are
   legal HTML5 but are invalid CSS selectors unescaped, which makes them hostile to
   `document.querySelector`, `label[for=…]` styling, and test selectors.

### Backwards compatibility

Change 4 alters generated ids for nested field names. Anyone selecting
`#settings\[theme\]` in CSS or JS breaks. Nested names are the minority case and the
current value is barely usable, so this is worth taking — but it is a **breaking change**
and must be called out in the changelog. Changes 1–3 only affect fields that pass an
explicit `id`, which is currently broken anyway.

### Acceptance criteria

- `<x-sleek::form-field name="foo" id="bar"/>` renders `<label for="bar">` and
  `<input id="bar" name="foo">`.
- Two instances of the same entity form with distinct `id` prefixes produce no duplicate
  ids and no cross-form label association.
- `<x-slot:label for="custom">` overrides the resolved id.
- Existing tests pass unchanged for fields that supply no `id`.
- Every field type (input, select, textarea, checkbox, radio-group, button-group) is
  covered by a test asserting label/control association.

---

## 2. Authentication assumes the default guard

**Report:** "Authentication assumes Laravel's default guard. The admin guard required a
custom Sleek navbar account partial."

### Today

Sleek's entire runtime auth surface is one line —
`navbar.blade.php:92`: `@if(Auth::check())`. That resolves
`config('auth.defaults.guard')`, normally `web`. There is no prop, no share, no config
key to point it elsewhere. An app whose admin panel runs on an `admin` guard shows a
logged-out navbar to a logged-in admin.

What *is* configurable is URLs only, via `SleekAuthenticationBuilder`:
`disable()`, `loginRoute()`, `loginUrl()`, `logoutRoute()`, `logoutUrl()`.

Two related problems in the same block:

- Both links are plain `GET` anchors (`navbar.blade.php:94,100`). Laravel Breeze and
  Jetstream both define `logout` as `POST`. Sleek's default therefore does not work with
  either scaffold out of the box.
- `route('logout')` is called unguarded, so an app that has authentication enabled but no
  route literally named `logout` throws a `RouteNotFoundException` while rendering the
  navbar.
- The language switcher (`navbar.blade.php:74-89`) is nested *inside* the
  `@unless(… === false)` auth block at `:70`, so `Sleek::authentication(false)` silently
  removes the language dropdown too.

### Proposed change

1. Add `guard(?string $guard)` to `SleekAuthenticationBuilder`, resolved through
   `Auth::guard($guard)->check()` in the navbar, defaulting to the current behaviour
   (default guard) when unset.
2. Add a `config/sleek.php` key `auth.guard` as the install-time default, since the guard
   is almost always fixed per deployment; the builder overrides it per request.
3. Add `logoutMethod('post'|'get')` to the builder, defaulting to `post` and rendering a
   CSRF-protected form-submit button styled as a link. This matches Breeze/Jetstream and
   is the correct HTTP semantic for a state-changing action.
4. Guard the `route()` calls: if the named route does not exist, omit the link rather
   than throwing. A missing logout link is a degraded navbar; an exception is a blank page.
5. Move the language switcher out of the auth `@unless` block so the two are independent.

### Backwards compatibility

Change 3 flips the default logout method. Apps relying on a `GET` logout route break
until they set `logoutMethod('get')`. Given that `GET` logout is a CSRF hazard and both
first-party scaffolds use `POST`, defaulting to `POST` is correct; ship it as a documented
breaking change with the one-line opt-out.

Change 5 means `Sleek::authentication(false)` now *keeps* the language switcher. Anyone
relying on the coupling to hide it should call `Sleek::language(false)`.

### Acceptance criteria

- `Sleek::authentication(fn ($a) => $a->guard('admin'))` renders the account block for a
  user authenticated on the `admin` guard and not for one on `web`.
- No custom navbar partial is needed for a non-default guard.
- Default install with Breeze produces a working logout without configuration.
- An app with no `logout` route renders the navbar without throwing.
- `Sleek::authentication(false)` leaves a configured language switcher visible.

---

## 3. No per-page title API

**Report:** "There is no per-page title API; the document and navbar read `APP_NAME`
directly."

### Today

`document.blade.php:7` is `<title>{{ env('APP_NAME') }}</title>`. The navbar brand does
the same at `:6,8`. There is no slot, prop, `@section('title')`, or `@yield` anywhere in
the layout chain (`view` → `document` → `page` → `layout`). Every page in an app has an
identical title.

The only escape hatch is replacing the entire document component via the `document` prop
or `Sleek::document(…)`. `document:`-prefixed attributes are forwarded
(`view.blade.php:3`) but land on the `<body>` tag (`document.blade.php:56`), so
`document:title="…"` produces a `title` attribute on `<body>` — a tooltip — rather than a
document title.

Compounding this, per **T2**, `env('APP_NAME')` returns `null` under `config:cache`, so
in production the title is empty and the brand is blank.

### Proposed change

1. Replace both `env('APP_NAME')` calls with `config('app.name')`. This is a bug fix and
   should ship independently of the rest, ahead of it if possible.
2. Add `Sleek::title(string $title)` to `SleekPageState`, shared as `sleek::title` by the
   view composer alongside the existing shares (`SleekServiceProvider.php:139-157`).
3. Add a `title` attribute on `<x-sleek::view>` as the ergonomic per-page form, which
   sets the same state — so `<x-sleek::view title="Publications">` works without touching
   a service provider.
4. Render as `{{ $title ? "$title — " . config('app.name') : config('app.name') }}`, with
   the separator and the whole template configurable via a `titleFormat` callable on the
   builder for apps that want `App · Page` or no suffix at all.
5. Leave the navbar brand reading `config('app.name')` — the brand is the *application*,
   not the page. Keep them separate concepts.

### Backwards compatibility

Change 1 changes rendered output for any app that has both a cached config and an
`APP_NAME` in `.env` — from empty to the actual name. That is strictly a fix. Changes 2–4
are additive.

### Acceptance criteria

- `<x-sleek::view title="Publications">` renders `<title>Publications — Acme</title>`.
- `Sleek::title()` set from a controller or middleware has the same effect.
- A page that sets no title renders `<title>Acme</title>`, unchanged from today's intent.
- Title renders correctly after `php artisan config:cache`.
- The navbar brand is unaffected by the page title.

---

## 4. Entity-table scoped slots and surrounding loop state

**Report:** "Entity-table scoped slots cannot access surrounding loop state. Publication
rows required temporary `is_current` and `tenant_slug` presentation attributes."

### This is a documentation bug, not a capability gap

**The capability has always existed.** Alongside `bind`, the patched compiler supports a
`use` attribute that mirrors the `use` keyword of a PHP closure and compiles to exactly
that — `src/Blade/BladeCompiler.php:78-82`:

```php
$uses = ! empty($matches['uses']) ? array_map('trim', explode(',', $matches['uses'])) : [];
$uses[] = '$__env';
$uses = implode(', ', $uses);
```

emitted at `:95` as `function ({$params}) use ({$uses}) { … }`. It is parsed in both
capture and legacy modes (`:60`), so it predates the scoped-slot registry entirely.

The reported blocker was therefore solvable on the day it was reported:

```blade
@foreach ($tenants as $tenant)
    <x-sleek::entity-table :entities="$tenant->publications">
        <x-slot:column-title bind="$value, $entity" use="$tenant, $currentId">
            <a href="{{ route('pub.show', [$tenant->slug, $entity]) }}">{{ $value }}</a>
            @if ($entity->id === $currentId) <span class="badge">current</span> @endif
        </x-slot:column-title>
    </x-sleek::entity-table>
@endforeach
```

No `is_current` or `tenant_slug` presentation attribute needed. The team added them
because nothing told them `use` exists.

**Why they could not have known.** `use=` appears **nowhere** in `docs/` or `README.md`
except inside a plan file (`docs/plans/2026-07-07-scoped-slot-registry.md:104`). The
custom-columns section of `docs/tables.md:201-251` explains `bind` in detail across five
paragraphs and two examples, and never mentions `use`. `docs/directives.md:212-219`
documents `scopedSlots` registration, also `bind`-only. The one place `use` now surfaces
in user-facing docs is a parenthetical added by the in-flight registry work
(`docs/tables.md:233-234`: *"an explicit `use=` still works if you want it"*) — which
mentions it as a legacy footnote to a feature that superseded it, without ever having
documented it as a feature in the first place.

This is a clean instance of the design principle's failure mode: the aggressive default
is well documented, the escape hatch is invisible. A consumer reading `docs/tables.md`
end to end comes away believing column slots are sealed from the surrounding template.

### What the in-flight registry work changes

The scoped-slot registry (`docs/plans/2026-07-07-scoped-slot-registry.md`, uncommitted)
makes `use` unnecessary for this case rather than newly possible. `compileEndSlot()` emits
`get_defined_vars()` at the definition site (`BladeCompiler.php:103-114`),
`Views\Factory::slot()` binds that snapshot into the callable
(`src/Views/Factory.php:7-24`), and the closure does `extract($__scope, EXTR_SKIP)`
(`:87`). Outer-scope variables become visible with no `use` clause — tested at
`tests/Unit/EntityTableColumnSlotTest.php:45-57`. Explicit `use` continues to work and
shadows the captured scope, since `EXTR_SKIP` will not overwrite a bound name.

So after that work lands there are two mechanisms with different semantics, and the
documentation currently describes neither properly:

| | `use="$x"` | implicit capture |
|---|---|---|
| Evaluated | at closure creation, per PHP `use` | snapshot at definition site |
| Shadowing | wins (`EXTR_SKIP` skips bound names) | loses to `use` and to bind params |
| Explicit in source | yes — reader sees the dependency | no |

### Residual code gaps

Real but small, and secondary to the documentation:

1. **The table's own row loop is not exposed.** A slot receives `$value` and `$entity` but
   not `$loop`, so first/last-row logic has no clean expression. Neither `use` nor
   implicit capture helps — the variable does not exist in the consumer's scope.
2. **The scope snapshot is taken once at definition, not per invocation** — stated at
   `docs/plans/2026-07-07-scoped-slot-registry.md:97-98`. Inherent to the mechanism;
   needs documenting, and is precisely the case where `use` remains the honest tool.
3. **The mandatory `bind` is a papercut.** The registry knows the names
   (`params: '$value, $entity'`) but refuses to inject them; the compile error even
   suggests the exact string to type (`ComponentTagCompiler.php:122-124`).
4. `entity-table.blade.php:40` renders `{{ ${$columnSlotName}->attributes }}` twice on the
   same `<td>` — unrelated, trivial, fix while in the file.

### Proposed change

**Primary — documentation.**

1. Document `use` as a first-class feature in `docs/tables.md`'s custom-columns section
   and in `docs/directives.md` alongside `bind`, with a worked example of pulling in an
   enclosing `@foreach` variable. Frame it by the closure analogy — it *is* the `use`
   keyword — which makes it self-explanatory to any PHP developer.
2. Once the registry work lands, document both mechanisms and when each applies, using
   the table above. State the definition-site snapshot caveat explicitly, and name `use`
   as the answer when a slot is defined outside the loop it depends on.
3. Add the "table inside a loop, slot needs the loop variable" case as an explicit recipe.
   It is the shape that generated this report and it deserves to be findable by scanning.

**Secondary — code.**

4. Add `$loop` as a third bind parameter for column slots
   (`params: '$value, $entity, $loop'`), passing the table's own row loop.
5. Allow `bind` to be omitted when the registry declares `params`, injecting the declared
   names; keep explicit `bind` working for renaming.
6. Ship the registry work — it is tested but not on `master`.

### Backwards compatibility

Documentation changes are free. Change 4 adds a parameter, so existing two-name binds keep
working. Change 5 relaxes a compile-time error, so nothing that compiles today stops.

### Acceptance criteria

- `docs/tables.md` documents `use` with a runnable example, discoverable by someone
  reading only the custom-columns section.
- The publications case is expressible with a documented API — and was, before any code
  change.
- A column slot can read `$loop->first` / `$loop->last` for its own row.
- `<x-slot:column-title>` with no `bind` compiles and receives the declared parameters.
- Both scope mechanisms and their precedence are documented in one place.

---

## 5. Alpine directives emitted, Alpine not installed

**Report:** "Forms emit Alpine directives, but Sleek does not install Alpine. Native
submission works, while dirty/loading enhancements remain inactive."

### Today

Sleek emits Alpine directives in two views:

- `form.blade.php:176` — `<form x-data="sleek__form" x-bind="form">`, with an `@once`
  block at `:11-48` registering `Alpine.data('sleek__form', …)` on `alpine:init`. This
  provides the `loading` flag, `isDirty` (delegating to a plain-JS `FormState` /
  `FormFieldObserver` at `:50+`), and a `beforeunload` guard gated on `prevent-unload`.
- `modal-form.blade.php:36-37,53-59` — `:disabled="loading"`, `x-show="loading"` on the
  spinner, and an `alpine:init` listener that unhides the spinner.

Nothing ships Alpine. `package.json:26-32` lists only `bootstrap`, `bootstrap-icons`,
`laravel-vite-plugin`, `sass-embedded`, `vite`. There is no `src/resources/js/` at all.
`SleekSetupCommand` (`src/Console/Commands/SleekSetupCommand.php:32-51`) installs and
wires up only `bootstrap` and `bootstrap-icons`. The provider publishes views and lang,
no JS.

Alpine is an **undeclared runtime dependency**. The failure mode is the worst kind: silent
and partial. Forms submit natively, so nothing looks broken; dirty tracking, the unload
guard, and submit spinners simply never fire, and there is no error to notice.

Tabs are unaffected — they use htmx plus Bootstrap's JS (`tabs/*.blade.php`), not Alpine.

### Options considered

**(a) Declare and install Alpine.** `sleek:setup` adds `alpinejs` to `package.json` and
appends the import + `Alpine.start()` to the app's JS entry, mirroring what it already
does for Bootstrap's Sass. Alpine.js is an established default in this stack and the
package already depends on it in practice — this makes the existing reality explicit.

**(b) Remove Alpine, reimplement in vanilla JS / Web Components.** The Alpine surface is
tiny (`x-data`, `x-bind`, `x-show`, `:disabled`) and the actual dirty-tracking logic is
*already* plain JS in `FormState`/`FormFieldObserver`. Alpine is doing very little work
here. This removes a dependency entirely and matches the "Web Components as the primary
building block outside SPA frameworks" preference.

**(c) Detect and warn.** Log a console warning in debug mode when directives are emitted
but `window.Alpine` is absent. Fixes the silence, not the gap.

**Recommendation: (a), with (c) as a cheap safety net.** (b) is the cleaner end state and
worth revisiting, but it is a rewrite of working code to remove a dependency the target
stack already assumes. (a) closes the reported gap with an addition to a command that
already does exactly this shape of work. Revisit (b) only if the Alpine surface grows.

### Proposed change

1. Extend `SleekSetupCommand` to detect, install, and wire `alpinejs` the same way it
   handles `bootstrap` — including appending `Alpine.start()` to the JS entry point.
2. Declare `alpinejs` in `package.json` and document it as a required peer.
3. Add a debug-mode-only console warning when `x-data="sleek__form"` is present and
   `window.Alpine` is undefined after DOMContentLoaded.
4. Document in `docs/forms.md` exactly which features require Alpine and what degrades
   without it — the current silence is the real defect.

### Backwards compatibility

Additive for new installs. Existing apps get nothing until they re-run `sleek:setup`;
the console warning tells them to.

### Acceptance criteria

- A fresh `sleek:setup` produces an app where dirty tracking and submit spinners work
  with no manual JS wiring.
- An app without Alpine logs one actionable warning in debug mode and otherwise behaves
  exactly as today.
- `docs/forms.md` lists the Alpine-dependent features explicitly.

---

## 6. `/lang/{locale}` route registered unconditionally

**Report:** "Sleek automatically registers the public `/lang/{locale}` route even when no
language switcher is configured."

### Today

`src/routes/web.php` is four lines — a single `GET lang/{locale}` that writes
`session()->put('locale', $locale)` and redirects back. It is registered unconditionally
at `SleekServiceProvider.php:201-203` inside `$this->booted(…)`, with no config flag and
no check for whether a language list is configured. `LocaleMiddleware` is likewise
appended to the `web` group unconditionally (`:212`).

Three problems beyond the reported one:

- **The route is unnamed**, and `navbar.blade.php:82` hardcodes `href="/lang/{{ $key }}"`.
  The route path is relative (`lang/{locale}`) while the link is root-absolute, so the
  switcher breaks when the app is served from a subdirectory.
- **The locale is unvalidated.** Any string is written to the session and passed to
  `App::setLocale()`. It should be constrained to the configured language list.
- **No auth middleware, no rate limit.** A public endpoint that writes to the session on
  every request is a small but free amplification surface.

### Proposed change

1. Register the route only when a language list is configured — check
   `SleekPageState`'s language configuration at boot, or gate on a
   `config('sleek.language.route')` flag for apps that configure languages per request.
2. Name the route `sleek.locale` and have the navbar use `route('sleek.locale', $key)`,
   fixing the subdirectory case.
3. Constrain the `{locale}` parameter with `->whereIn()` against the configured list, so
   unknown locales 404 rather than poisoning the session.
4. Gate `LocaleMiddleware` on the same condition.
5. Allow the route path to be overridden via config for apps with a conflicting `/lang`
   URL.

### Backwards compatibility

Apps that configure languages see no change. Apps that do not lose an endpoint they were
not using — which is the point of the report. An app that somehow relied on
`GET /lang/xx` without configuring a switcher must set the config flag.

### Acceptance criteria

- A default install with no language configuration registers zero routes and adds no
  middleware.
- With languages configured, the switcher works from both the domain root and a
  subdirectory.
- An unconfigured locale returns 404 and does not alter the session.

---

## 7. Responsive table CSS is global with a fixed breakpoint

**Report:** "Sleek's responsive table CSS is global and uses a fixed 600px breakpoint."

### Today

`entity-table.blade.php:58-110` is an inline `<style>` block using bare element selectors:

```css
@media screen and (max-width: 600px) {
    table { border: 0; }
    table thead { … clip: rect(0 0 0 0); position: absolute; }
    table tbody { display: block; }
    table tr { display: block; … }
    table td { display: block; text-align: right; }
    table td::before { content: attr(data-label); … }
}
```

This targets **every** `table` on the page, including tables the consuming app renders
itself, and it is emitted once per entity-table instance with no `@once` — two tables on a
page means the same global rules twice.

Additionally, `EntityTable` declares `public bool $responsive = false`
(`src/Views/Components/EntityTable.php:21`) but `entity-table.blade.php:7` never forwards
it to `<x-bs::table>`. The prop is inert, and the stack-into-cards CSS is unconditionally
active — there is no way to turn it off.

`pagination.blade.php:105-127` has a parallel 600px block; that one *is* class-scoped, but
includes one brittle selector (`.flex-fill.d-flex.align-items-center.gap-2.mb-2` at `:124`)
that couples CSS to a specific utility-class string.

### Proposed change

1. Move the rules into `src/resources/sass/` (see **T1**) and scope every selector under a
   `.sleek-table-stack` class applied by the entity-table itself. No bare element
   selectors escape into the host app.
2. Drive the breakpoint from a Sass variable defaulting to Bootstrap's `md` (768px), so
   consumers override it the standard Sass way. See item 8 on why `md` rather than 600px.
3. Honour the existing `$responsive` prop: forward it to `<x-bs::table>`, and add an
   explicit `stack` prop (default `true`) controlling whether the stack-into-cards
   treatment applies at all. `stack="false"` gives a plain horizontally-scrolling table.
4. Replace the brittle utility-class selector in `pagination.blade.php:124` with a
   dedicated class.

### Backwards compatibility

Moving to Sass means apps that do not compile Sleek's Sass lose the styling. `sleek:setup`
already wires the Sass import (`SleekSetupCommand.php:113`), so this is only an issue for
apps that bypassed setup — they need one `@import` line. Call it out in the changelog.

Changing the breakpoint from 600px to 768px changes behaviour in the 600–768px band. This
is deliberate: 600px is not on any scale the rest of the app uses, and tables stacking at a
different width than the sidebar collapses is the root of item 8's incoherence.

### Acceptance criteria

- A table rendered by the consuming app is unaffected by Sleek's responsive CSS.
- Two entity tables on one page emit the stacking rules once.
- The breakpoint is overridable from the app's Sass with no `!important`.
- `<x-sleek::entity-table :stack="false">` renders a non-stacking table.

---

## 8. Inconsistent breakpoints across the layout

**Report:** "Sidebar and Bootstrap navbar collapse at different breakpoints — 799px versus
Bootstrap's lg breakpoint."

### Today

There are **three** unrelated thresholds, none of them on Bootstrap's scale
(576 / 768 / 992 / 1200 / 1400):

| Threshold | Where | What it controls |
|---|---|---|
| 992px (`lg`) | `navbar.blade.php:3` — `navbar-expand-lg` | Bootstrap's own collapse toggle |
| 799px | `navbar.blade.php:123,143`, `page/index.blade.php:32` | Sidebar column layout, sticky nav, grid columns |
| 600px | `entity-table.blade.php:58`, `pagination.blade.php:105` | Table stacking, pagination layout |

Between 799px and 992px the sidebar has switched to its wide layout while Bootstrap still
considers the navbar collapsed — the visible symptom in the report.

Bootstrap's breakpoints are at defaults. `src/resources/sass/app.scss:1-8` overrides only
`$enable-negative-margins`, and does so *after* `@import "bootstrap/scss/variables"`, so
even that has no effect on `!default` resolution. `$grid-breakpoints` is never overridden,
and no `media-breakpoint-up` mixin is used anywhere — every media query is a hardcoded
pixel value in an inline `<style>`.

### Proposed change

1. Fix the variable-ordering bug in `app.scss`: set all Sleek variable overrides
   **before** `@import "bootstrap/scss/variables"`. `$enable-negative-margins` is currently
   a no-op.
2. Move all layout media queries out of inline `<style>` into Sass (**T1**) and express
   them with `media-breakpoint-up($sleek-sidebar-breakpoint)`, defaulting to `lg` so the
   sidebar and Bootstrap's navbar collapse together.
3. Define a small set of named Sleek breakpoint variables — `$sleek-sidebar-breakpoint`
   (default `lg`), `$sleek-table-stack-breakpoint` (default `md`) — as `!default` so
   consumers override them in Sass before importing Sleek.
4. Document the breakpoint variables in `docs/layout.md`.

### Backwards compatibility

Moving the sidebar breakpoint 799px → 992px changes layout in the 799–992px band. This is
the reported bug, so the change is the point; note it in the changelog for anyone who
tuned around the old value, with the one-line Sass override to restore it.

Fixing the `$enable-negative-margins` ordering will actually enable negative margin
utilities for the first time. This adds CSS classes; it does not change existing rendering.

### Acceptance criteria

- Sidebar and navbar transition at the same width by default.
- Setting `$sleek-sidebar-breakpoint: md;` before the import moves both.
- No hardcoded pixel media query remains in any Blade view.
- Negative-margin utilities are present in the compiled CSS.

---

## Sequencing

Grouped by dependency and by whether the change is a fix or a feature. No time estimates —
scope is given as files touched.

**Wave 0 — documentation only, ship immediately.** No code, no risk, and it retires the
most expensive report in the set.
- Item 4, changes 1–3: document `use` in `docs/tables.md` and `docs/directives.md`. The
  consuming team can drop their `is_current` / `tenant_slug` columns as soon as this lands
  — before any code ships.

**Wave 1 — bugs, ship independently.** Each is small and unblocks a later item.
- T2: `env('APP_NAME')` → `config('app.name')` (2 views). Unblocks item 3.
- Item 4, change 6: land the scoped-slot registry work already in the tree, and fold its
  implicit-capture semantics into the docs written in wave 0.
- Item 2, changes 4–5: guard `route()` calls, decouple the language switcher (1 view).
- Item 7, change 3: forward the inert `$responsive` prop (1 view).

**Wave 2 — configuration surface.** Introduces `config/sleek.php` (T3); items 2 and 6 both
depend on it.
- Item 2: guard configurability, POST logout.
- Item 6: conditional lang route, naming, validation.

**Wave 3 — Sass migration.** T1, done once for items 7 and 8 together; splitting them means
touching the same views twice.
- Item 8, change 1 (variable ordering) first, since the breakpoint variables depend on it.
- Items 7 and 8 in one pass across `entity-table`, `navbar`, `page/index`, `pagination`.

**Wave 4 — additive APIs.**
- Item 3: `Sleek::title()` and the `title` attribute.
- Item 5: Alpine in `sleek:setup`, plus the debug warning.
- Item 1: label/id association. Grouped here rather than with the bugs because change 4
  (id generation for nested names) is breaking and wants a version boundary.

## Breaking changes summary

For the changelog. All have a one-line opt-out.

| Change | Item | Opt-out |
|---|---|---|
| Default id for nested names: `a[b]` → `a-b` | 1 | Pass `id` explicitly |
| Logout defaults to POST | 2 | `->logoutMethod('get')` |
| `authentication(false)` no longer hides the language switcher | 2 | `Sleek::language(false)` |
| Table stacking breakpoint 600px → `md` | 7 | `$sleek-table-stack-breakpoint: 600px;` |
| Sidebar breakpoint 799px → `lg` | 8 | `$sleek-sidebar-breakpoint: 799px;` |
| Responsive table CSS requires compiling Sleek's Sass | 7 | Add the `@import` |
| Lang route not registered without configured languages | 6 | `config('sleek.language.route')` |

## Out of scope

- Replacing Alpine with Web Components (item 5, option b) — revisit if the Alpine surface
  grows beyond the current five directives.
- The `old()` / `@error()` dotted-vs-bracketed name inconsistency found while
  investigating item 1 (`FormField.php:45-46` vs `wrapper.blade.php:18,21,32`): `old()`
  runs before the bracket rewrite and error lookups run after, so nested-field error
  display and value repopulation disagree. Real bug, unrelated to this feedback, needs its
  own ticket.
- The unprefixed global component aliases `icon`, `wrap-with`, `apply`
  (`SleekServiceProvider.php:49-51`), which will collide with a host app's own `x-icon`.
  Separate ticket.
- Untagged view/lang publishing (`SleekServiceProvider.php:192-194,215-217`), which makes
  `vendor:publish --tag=…` unable to target them. Separate ticket.

## Documentation impact

Per the project's definition of done, these land with their code:

- `docs/forms.md` — Alpine requirement and degradation (5); label/id association (1).
- `docs/layout.md` — page title API (3); guard configuration and POST logout (2);
  breakpoint variables (8).
- `docs/tables.md` — **`use` as a documented feature, plus implicit-capture semantics and
  the snapshot caveat (4) — this is the deliverable for item 4, not a side effect of it**;
  `stack` prop and table breakpoint (7).
- `docs/directives.md` — `use` alongside `bind` in the `scopedSlots` section (4).
- `docs/navigation.md` — language switcher configuration and route naming (6).
- `CHANGELOG.md` — the breaking changes table above.
- New: `config/sleek.php` reference, wherever install-time configuration is documented.
