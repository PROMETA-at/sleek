# Scoped-Slot Registry: Compiler-Driven Lazy & Parameterized Slots

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Supersedes** the option section of
> [`2026-06-19-lazy-tab-content-rendering.md`](2026-06-19-lazy-tab-content-rendering.md) (this is the "real
> implementation plan" that spec asked for). **Related:**
> [`2026-06-19-deferred-content-component.md`](2026-06-19-deferred-content-component.md) — lazy tabs subsume
> the *tab* use case of `x-sleek::deferred`; the deferred component remains a separate, still-unscheduled PRD
> for non-tab progressive loading.

**Goal:** Stop tab slot bodies from executing for inactive tabs (they currently run on every render and are
discarded), and remove the "consumer MUST write `bind=` or it fatals at runtime" trap on entity-table column
slots — both via one generalized mechanism: components register slot-name patterns with Sleek's Blade
compiler, and matching slots compile to closures.

**Architecture:** A regular Blade slot's body executes at *capture* time — between `$__env->slot()` and
`$__env->endSlot()` in the compiled **consumer** template, before the component's own view ever runs. Output
buffering captures the output but cannot un-execute the PHP, so no change inside `Tab` or `index.blade.php`
can prevent the work. The only construct that defers execution is a closure, and Sleek already owns the
machinery for closure slots (`bind` → `CallableComponentSlot` via the custom `ComponentTagCompiler` /
`BladeCompiler` / `Views\Factory`). This plan generalizes it:

1. A **registry** on Sleek's `BladeCompiler`: components declare, at boot time, which of their slot-name
   patterns compile as scoped (closure-backed) slots.
2. A **component-stack scanner** in `ComponentTagCompiler::compileSlots()`: since slots compile *before*
   component tags, the raw `<x-...>` tags are still literal text, so a document-order scan attributes every
   slot to its nearest enclosing component tag. Only slots inside registered components get the scoped
   compile; everything else compiles byte-identically to today.
3. An **implicit scope capture** (`get_defined_vars()` at the definition site, `extract(..., EXTR_SKIP)`
   inside the closure) so slot bodies keep normal Blade variable semantics without an explicit `use=`.

The design rule that keeps this non-magical: **the compiler never invents variables** — it either defers
execution (zero-arg mode, invisible to the template author) or demands the consumer name what they receive
(parameterized mode, explicit `bind` enforced with a compile-time error).

**Tech Stack:** PHP only — `src/Blade/ComponentTagCompiler.php`, `src/Blade/BladeCompiler.php`,
`src/Views/Factory.php`, `src/Views/CallableComponentSlot.php`, `src/resources/views/components/tabs/index.blade.php`,
`SleekServiceProvider`. No JS/asset changes. Tests via PHPUnit + Testbench (`$this->blade(...)` as in
`tests/Unit/BtnComponentTest.php`).

---

### Background: the two consumers

**Tabs** (`src/resources/views/components/tabs/index.blade.php:19`) eagerly renders every slot at
construction (`$slot->toHtml()`), then `Tab::toHtml()` discards the content for inactive tabs. Expensive tab
bodies (aggregate queries, API calls) run on every page load and every tab switch. `docs/tabs.md` already
documents `$tab->content` as "only populated for the active tab" — the contract is lazy; the implementation
isn't.

**Entity-table** (`src/resources/views/components/entity-table.blade.php:41`) invokes column slots as
callables per row: `${$columnSlotName}($value, $entity)`. Consumers must write
`bind="$value, $entity"`; forgetting it produces a runtime fatal (`ComponentSlot` is not callable) deep in
the row loop.

### Registry API

```php
// SleekServiceProvider (and public API for consumers' own components):
$bladeCompiler->scopedSlots('sleek::tabs*', 'tab-*');                                     // zero-arg mode
$bladeCompiler->scopedSlots('sleek::entity-table', 'column-*', params: '$value, $entity'); // parameterized mode
```

- **Zero-arg mode** (no `params`): matching slots auto-compile as argument-less closures. Nothing new
  appears in scope; the body sees exactly the variables it sees today (via scope capture). A `bind` on such
  a slot is a **compile-time error** ("receives no arguments" — it could only fail at runtime).
- **Parameterized mode** (`params` given): consumers keep writing `bind="$value, $entity"` explicitly. A
  matching slot **without** `bind` is a compile-time error whose message uses the registered `params` string
  purely as the suggestion — the compiler never injects those names.

Registration must happen at provider boot (before any consumer template compiles). Compiled output depends
on the registry, so changing registrations requires `php artisan view:clear` — a docs note, same class of
caveat as any compiler extension.

### Compiled forms

Zero-arg (`<x-slot:tab-overview label="Overview">…`), emitted directive
`@slot('tabOverview', [...] bind () capture)`:

```php
<?php $__env->slot('tabOverview', function ($__scope) use ($__env) { extract($__scope, EXTR_SKIP); ?>
    ...body, executes only when invoked...
<?php }, [...attrs...], get_defined_vars()); ?>
```

Parameterized (`<x-slot:column-date bind="$value, $entity">…`), emitted directive
`@slot('columnDate', [...] bind ($value, $entity) capture)`:

```php
<?php $__env->slot('columnDate', function ($__scope, $value, $entity) use ($__env) { extract($__scope, EXTR_SKIP); ?>
    ...body...
<?php }, [...attrs...], get_defined_vars()); ?>
```

Notes on the shape:

- `get_defined_vars()` sits in the `@endslot` output — after the closing `}`, back in template scope, so it
  captures the definition-site variables once, not per invocation.
- Closure parameters are declared before `extract` runs; `EXTR_SKIP` ensures params (and `use`d vars) shadow
  captured scope. `$loop`, local variables, everything works without `use=`.
- `Factory::slot()` binds the scope into the callable (`fn (...$args) => $content($scope, ...$args)`), so
  invocation sites are unchanged: tabs invoke with zero args, entity-table with `($value, $entity)`.
- An explicit `use="..."` on a registered slot is still honored (appended to the closure's `use` list) for
  backward compatibility — it's just no longer necessary.
- Legacy `bind` slots in **unregistered** components compile exactly as today (no `capture` marker, no scope
  argument). The `capture` marker in the directive grammar is what distinguishes registry-matched slots.

### Scanner: attributing slots to components

`ComponentTagCompiler::compile()` runs `compileSlots()` before `compileTags()`, so component tags are still
raw text. Replace the flat slot regex pass with a single document-order scan over component open tags,
self-closing tags, closing tags, and slot tags (all with `PREG_OFFSET_CAPTURE`), maintaining a component-name
stack. Each slot consults the registry against the stack top; only slot tags are rewritten — component tags
pass through untouched for `compileTags()`.

```
<x-i-need-scoped-slots>          → push
  <x-slot:i-am-scoped>           → stack top registered → scoped compile
  <x-i-need-regular-slots>       → push
    <x-slot:i-am-not-scoped>     → stack top NOT registered → plain @slot, byte-identical to today
  </x-i-need-regular-slots>      → pop
</x-i-need-scoped-slots>         → pop
```

Invariants:

- **Reuse `compileTags()`'s own regexes** (open / self-closing / closing, both `x-` and `x:` forms) so
  attribution can never disagree with what actually compiles as a component. Inherits Laravel's existing
  blindspots (e.g. literal `<x-` inside `@verbatim`) without adding new ones.
- Self-closing components are recognized but never pushed (otherwise the stack skews and every subsequent
  slot mis-attributes).
- Slot tags never push onto the component stack; after a nested component closes, attribution returns to the
  outer component.
- Slot names match the registry pattern **as written** (`tab-overview`, before camelization); dynamically
  named slots (`:name="$expr"`) cannot be matched at compile time and compile plain.
- **Graceful degradation:** anything unattributable (`<x-dynamic-component>`, mismatched closing tags,
  exotic aliasing) compiles eagerly — the status quo, never a new failure mode. In parameterized mode a miss
  only means the compile-time check doesn't fire and you get today's runtime error: degraded diagnostics,
  not degraded behavior.
- The existing `BladeCompiler::$slotStack` pairing between `compileSlot` / `compileEndSlot` carries the new
  `capture` flag (directives compile in document order, which that mechanism already relies on).

### Out of scope

- The `x-sleek::deferred` component (separate PRD; the dashboard use case stands on its own).
- Self-closing slot syntax (`<x-slot:foo />`) — not supported by the compiler today, stays that way.
- Registering further Sleek components (`entity-card-list`, `entity-view`, form-field slots, …). Audit them
  as a follow-up once the mechanism is proven on tabs + entity-table.
- An `:eager` escape hatch on the tabs component (invoke every closure at construction). Trivial to add
  later if anyone turns out to rely on inactive-tab side effects; not built speculatively.

---

### Task 1: Runtime foundations — `CallableComponentSlot` + `Factory::slot()` scope binding

**Files:**
- Modify: `src/Views/CallableComponentSlot.php`
- Modify: `src/Views/Factory.php`

- [ ] **Step 1: Make `CallableComponentSlot` renderable.** Implement `Htmlable` and `Stringable`:
  `toHtml()` invokes the callable with zero args inside `capture()` (`Prometa\Sleek\capture`) and returns the
  output. This lets the tabs view render active slots without `instanceof` checks and hardens general
  interop (echoing a zero-arg callable slot works; echoing a parameterized one is a usage error regardless).
  Fix the stale `@var string` docblock on `$callable` while in there.

- [ ] **Step 2: Add the scope parameter to `Factory::slot()`.**
  Signature becomes `slot($name, $content = null, $attributes = [], ?array $scope = null)`. When `$content`
  is callable and `$scope !== null`, wrap: `fn (...$args) => $content($scope, ...$args)` before constructing
  the `CallableComponentSlot`. **`$scope` must default to `null` and re-wrapping must not occur when it is
  null** — `@forwardSlots` (SleekServiceProvider) re-registers already-bound `CallableComponentSlot`s via
  `$__env->slot($slotName, $slotContent, ...)` with no scope argument; the pass-through must not double-bind.

### Task 2: `BladeCompiler` — compile the `capture` directive variant

**Files:**
- Modify: `src/Blade/BladeCompiler.php`

- [ ] **Step 1: Extend `compileSlot()`.** Grammar gains an optional trailing `capture` token:
  `@slot({name}, {attrs} bind ({bindings}) [use ({uses})] capture)`. When present, emit the closure with
  `$__scope` prepended to the parameter list and `extract($__scope, EXTR_SKIP);` as the first statement
  (see "Compiled forms" above). Push the flag onto `static::$slotStack`.

- [ ] **Step 2: Extend `compileEndSlot()`.** When the popped slot meta carries the flag, emit
  `<?php }, {attributes}, get_defined_vars()); ?>` instead of the two-argument close.

- [ ] **Step 3: Unit-test the compiled string directly** (a `BladeCompiler::compileString()`-level test):
  zero-arg form, parameterized form, parameterized + `use=` form, and the legacy scoped form (no `capture`)
  which must compile byte-identically to before this change.

### Task 3: `ComponentTagCompiler` — registry + component-stack scanner + enforcement

**Files:**
- Modify: `src/Blade/ComponentTagCompiler.php`
- Modify: `src/Blade/BladeCompiler.php` (registry storage + public `scopedSlots()` method)

- [ ] **Step 1: Registry storage.** `BladeCompiler::scopedSlots(string $componentPattern, string $slotPattern, ?string $params = null)`
  appends to an instance-level registry; `ComponentTagCompiler` reads it via the `$blade` reference it is
  constructed with. Pattern matching via `Str::is()` on both component tag name (as written in the template,
  e.g. `sleek::tabs.pills`) and raw slot name.

- [ ] **Step 2: Replace the flat pass in `compileSlots()` with the document-order scanner** described above.
  Preserve the existing output for every slot that doesn't match the registry (byte-identical — this is the
  regression bar). For matches, emit the `capture` directive form: zero-arg registrations get `bind ()`;
  parameterized registrations pass the consumer's `bind` expression through.

- [ ] **Step 3: Enforcement errors.** Throw `InvalidArgumentException` during compilation (consistent with
  Laravel's unknown-component errors, which surface with template context):
  - Parameterized match without `bind`:
    `Slot [column-date] of component [sleek::entity-table] is scoped and receives arguments — declare them, e.g. bind="$value, $entity"`.
  - Zero-arg match with `bind`:
    `Slot [tab-overview] of component [sleek::tabs] receives no arguments — remove the bind attribute`.

- [ ] **Step 4: Nesting tests** (compile-level and/or rendered via `$this->blade()`):
  - Unregistered component nested inside a registered one: inner slots compile plain (the example from the
    design discussion).
  - Registered component nested inside a registered component's slot body (tabs-in-tabs): both levels
    scoped; inner `$__env->slot()` calls only execute when the outer closure is invoked (transitive
    laziness).
  - Self-closing component between slots does not skew attribution.
  - Slot after a nested component closes attributes to the outer component again.
  - `<x-dynamic-component>` and a registered slot name inside an *unregistered* component both compile
    eagerly.

### Task 4: Tabs — register and render lazily

**Files:**
- Modify: `src/Providers/SleekServiceProvider.php`
- Modify: `src/resources/views/components/tabs/index.blade.php`

- [ ] **Step 1: Register** in the existing `callAfterResolving('blade.compiler', ...)` block:
  `$bladeCompiler->scopedSlots('sleek::tabs*', 'tab-*');` — covers the base component and all four presets
  (`.pills`, `.vertical`, `.card`, `.collapse`; consumer slots are written on the preset tag and forwarded
  at runtime by `@forwardSlots`, which passes `CallableComponentSlot`s through unchanged). The pattern
  deliberately does not match `sleek::tab` / `sleek::tab-header`.

- [ ] **Step 2: Render only the active slot** in `index.blade.php`. In the `TabCollection` construction,
  replace `$slot->toHtml()` with `$key === $activeSlot ? $slot->toHtml() : ''`. With Task 1's `Htmlable`
  implementation this works for both `CallableComponentSlot` (compiled path — closure runs here, exactly
  once per request) and plain `ComponentSlot` (any degraded/programmatic path — keeps today's behavior).
  Everything else stays untouched: slot *attributes* (`label`, `href`) remain eager for the nav links; label
  slots (`labelOverview`) don't match `tab-*` and stay eager; the HTMX fragment block at the bottom uses the
  already-rendered `$tabs->current()` (on a fragment request the requested tab *is* the active one).

- [ ] **Step 3: Behavior tests** (`tests/Unit/`, Testbench `$this->blade()`; for query-param / HTMX paths,
  swap a crafted `Request` into the container before rendering):
  - Inactive tab body with an observable side effect (static counter on a test helper class) does **not**
    execute; the active tab's does. This is the key regression test from the June spec.
  - Active tab renders inline on first load (content present in output).
  - `?tab=key` present → that tab renders, first tab's body does not execute.
  - HTMX fragment request (`HX-Request` header + `?tab=key`) → response contains the requested tab's
    rendered content.
  - Headless API: `$tab->content` populated only for the active tab; `bind="$tabs"` default slot unchanged.
  - Scope capture: tab body references a variable passed via `$this->blade($template, ['user' => …])` and a
    `@foreach` `$loop` — both resolve. This guards the `get_defined_vars()` mechanism.

### Task 5: Entity-table — register and verify diagnostics

**Files:**
- Modify: `src/Providers/SleekServiceProvider.php`

- [ ] **Step 1: Register**
  `$bladeCompiler->scopedSlots('sleek::entity-table', 'column-*', params: '$value, $entity');`.
  `entity-table.blade.php` itself needs **no changes** — it already invokes column slots as callables.

- [ ] **Step 2: Tests:**
  - Column slot with `bind="$value, $entity"` renders per row exactly as today.
  - Column slot **without** `bind` → compile-time `InvalidArgumentException` containing the suggested
    `bind="$value, $entity"` (previously: runtime fatal).
  - Column slot body referencing an outer-scope variable without `use=` → resolves (the `use=` friction is
    gone); an explicit `use=` still compiles and works.

### Task 6: Documentation

**Files:**
- Modify: `docs/tabs.md`
- Modify: `docs/tables.md`
- Modify: `docs/implicit-behaviors.md`
- Modify: `docs/directives.md` (or `components.md`, whichever hosts compiler-level API better)
- Modify: `docs/plans/2026-06-19-lazy-tab-content-rendering.md` (status header → implemented via this plan)

- [ ] **Step 1: `docs/tabs.md`** — the `$tab->content` contract line is now true; add a short note that
  inactive tab bodies do not execute at all (queries, API calls in inactive tabs cost nothing until the tab
  is shown), and that side effects (`@push` etc.) in inactive tabs run when the tab is fetched, matching the
  HTMX fragment path.

- [ ] **Step 2: `docs/tables.md`** — column slots: `bind="$value, $entity"` is required and now enforced
  with a clear compile-time error; `use=` is no longer needed for outer-scope access.

- [ ] **Step 3: `docs/implicit-behaviors.md`** — one entry: slots named `tab-*` inside `x-sleek::tabs*` and
  `column-*` inside `x-sleek::entity-table` compile to closures; inactive/uninvoked bodies never execute;
  registry changes require `view:clear`.

- [ ] **Step 4: Public registry API** — document `scopedSlots()` for consumers building their own
  components: both modes, the design rule (defer invisibly or name explicitly, never inject), boot-time
  registration requirement, `view:clear` caveat, graceful-degradation semantics.

### Task 7: Release

- [ ] **Step 1: Bump minor version in `package.json`** — new public compiler API plus a behavior change.

- [ ] **Step 2: Changelog / release notes call-outs:**
  - Feature: scoped-slot registry (`scopedSlots()`), available to consumer components.
  - Fix: inactive tab bodies no longer execute (matches the documented `$tab->content` contract).
  - Behavior change: side effects in inactive tab slot bodies (counters, `@push`, `@once`) no longer run on
    initial load — they run when the tab is fetched. Audit consumers relying on this (the June spec's open
    question 2); none are expected.
  - Improvement: forgetting `bind` on an entity-table column slot is now a compile-time error instead of a
    runtime fatal.
  - Note: run `php artisan view:clear` after upgrading (compiled slot output changed).

---

### Verification checklist (before merging)

- [ ] The June spec's empirical repro is fixed: a `tabs.card` whose **inactive** tab body calls a method on
  `null` renders without error; making that tab active via `?tab=` reproduces the exception (proof the body
  runs only when active).
- [ ] All four presets + headless mode render correctly with lazy slots (nav labels, active pane content,
  `hx-*` link attributes unchanged in output).
- [ ] HTMX tab switching works end-to-end in the workbench app (`composer serve`): fragment swap, `hx-swap-oob`,
  URL push.
- [ ] No-htmx degradation: full-page reload with `?tab=key` renders the right tab.
- [ ] entity-table in the workbench renders sorted, paginated, with custom column slots — before/after HTML
  identical for correct existing usage.
- [ ] A template that uses **no** registered components compiles byte-identically to before (diff the
  compiled output of a representative workbench view).
- [ ] `composer test` and `composer lint` pass.
- [ ] `online-systembrett`'s statistics-tab workaround (htmx-deferred placeholder inside a tab) can be
  removed in a follow-up: the aggregate queries no longer run when the Videos tab is active.
