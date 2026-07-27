# Dirty Tracking: Defer Initial-Value Capture

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make sleek's form-dirty tracking robust against silent DOM writes that happen during page hydration — most notably Alpine `x-model` directives in nested `x-data` blocks that flip `.checked` / `.value` after `FormFieldObserver` has already taken its baseline.

**Architecture:** `FormFieldObserver` currently captures `#initialValue = this.value` synchronously inside its constructor, which is called from the form's Alpine `init()`. Alpine walks the tree top-down and synchronously runs nested `x-data` inits (and their `x-model` directives) *after* the parent's `init()` returns. Those nested directives write to DOM properties without dispatching `input`/`change`, so the field's current value silently diverges from `#initialValue`. The next time anything reads `field.isDirty` (e.g. the `beforeunload` poll in `sleek__form`), the comparison reports dirty.

The fix is to capture the baseline one microtask later. By the next microtask Alpine's synchronous tree walk has completed and all nested `x-model` writes have landed; `this.value` then reflects the post-hydration state, which is the correct baseline. A `#ready` flag prevents `isDirty` from reporting against an undefined baseline in the (essentially impossible) gap between construction and the microtask.

**Tech Stack:** Vanilla JS inside `src/resources/views/components/form.blade.php`. No new dependencies, no build changes.

---

### Background: the failing scenario

Reproduction in the consuming admin panel (online-systembrett, `video-offers/edit.blade.php`):

```blade
<div x-data="{ addMode: 'select', ... }">
  <input type="radio" name="preview_video_add_mode_de" value="select" x-model="addMode">
  <input type="radio" name="preview_video_add_mode_de" value="upload" x-model="addMode">
</div>
```

Neither radio carries the HTML `checked` attribute. The Alpine variable `addMode` is `'select'`. Sequence on load:

1. The outer `<form x-data="sleek__form">` Alpine `init()` runs. `FormState` constructs a `FormFieldObserver` for each `[name]` element in the form. For the `select` radio, `#initialValue = false` (HTML default; no `checked`).
2. Alpine continues its sync tree walk into the inner `x-data`. Its `init` runs; `x-model` then applies `addMode → DOM`, writing `el.checked = true` on the matching radio. No `change` event is dispatched.
3. `field.isDirty` getter is `initialValue=false !== value=true → true`. Polled by `formState.isDirty` (e.g. from `beforeunload`), the form reports dirty even though the user did nothing.

The application worked around this with the existing `dirty="false"` opt-out. That opt-out remains useful for fields that are pure UI state, but the underlying race deserves a real fix because it will keep biting consumers using nested `x-data` + `x-model`.

### Why a microtask is the right place

- Alpine.start() walks the tree synchronously. All `x-data` inits and directive applies run within one task.
- `queueMicrotask(fn)` schedules `fn` after the current task completes — i.e. after Alpine's sync walk, but before paint and before any user event.
- `this.value` read inside that microtask therefore reflects post-hydration DOM state.
- Custom-element `upgrade` events (used for `sleek-select`, `video-direct-upload`, etc.) already had a deferred path. The new path matches that semantics for the non-custom-element case.

### Out of scope

- HTMX swaps that introduce new `[name]` elements after initial load. `FormState.initMetadata()` only runs once at construct time; new fields aren't tracked. Pre-existing limitation, not made worse here.
- Async `x-init` that fetches and writes back later. Anything that mutates the field after the initial microtask correctly counts as a "real" change. Consumers that need it ignored should use `dirty="false"`.

---

### Task 1: Defer initial-value capture in `FormFieldObserver`

**Files:**
- Modify: `src/resources/views/components/form.blade.php`

- [ ] **Step 1: Replace the constructor's sync capture with a microtask + ready guard**

Current (lines ~84–104):

```js
class FormFieldObserver {
  #el
  #initialValue
  #isDirty = false

  constructor(el) {
    this.#el = el
    if (isCustomElement(el.tagName) && !isCustomElementConnected(el)) {
      el.addEventListener('upgrade', () => {
        this.#initialValue = this.value
      }, { once: true })
    } else {
      this.#initialValue = this.value
    }

    this.#el.addEventListener('input', this.updateDirtyState.bind(this))
    this.#el.addEventListener('change', this.updateDirtyState.bind(this))
  }
  // ...
}
```

Replace with:

```js
class FormFieldObserver {
  #el
  #initialValue
  #isDirty = false
  #ready = false

  constructor(el) {
    this.#el = el

    const captureInitial = () => {
      this.#initialValue = this.value
      this.#ready = true
    }

    if (isCustomElement(el.tagName) && !isCustomElementConnected(el)) {
      // Defer until the custom element finishes its connectedCallback and
      // signals it has populated its value.
      el.addEventListener('upgrade', captureInitial, { once: true })
    } else {
      // Defer one microtask so any post-construction DOM writes from
      // sibling/nested Alpine x-data + x-model bindings have already landed.
      // Alpine's tree walk is synchronous, so the next microtask runs after
      // the whole tree has been initialized.
      queueMicrotask(captureInitial)
    }

    this.#el.addEventListener('input', this.updateDirtyState.bind(this))
    this.#el.addEventListener('change', this.updateDirtyState.bind(this))
  }
  // ...
}
```

- [ ] **Step 2: Make `isDirty` honor the ready guard**

Current (lines ~116–121):

```js
get isDirty() {
  if (this.#el.hasAttribute('dirty') && !!this.#el.getAttribute('dirty'))
    return !!safeEval(this.#el.getAttribute('dirty'))

  return !valueEqual(this.initialValue, this.value)
}
```

Replace with:

```js
get isDirty() {
  if (this.#el.hasAttribute('dirty') && !!this.#el.getAttribute('dirty'))
    return !!safeEval(this.#el.getAttribute('dirty'))

  // Until the baseline has been captured, treat the field as clean. This
  // closes the (vanishingly small) gap between constructor and microtask /
  // upgrade event — without it, isDirty would compare against an undefined
  // baseline if something dispatched input/change in that window.
  if (!this.#ready) return false

  return !valueEqual(this.initialValue, this.value)
}
```

- [ ] **Step 3: Verify by running the test page locally**

Use any consuming app that exhibited the original bug (e.g. online-systembrett `video-offers/edit.blade.php` with `dirty="false"` removed from the `preview_video_add_mode_*` radios).

Expected on a fresh page load:
- `Alpine.$data(form).isDirty` is `false`
- `formDirty` Alpine variable on the parent view is `false`
- Navigating away does **not** trigger the `beforeunload` "Leave site?" dialog

---

### Task 2: Add a unit/integration test

**Files:**
- Add: `tests/Unit/FormDirtyTrackingTest.php` *(or whatever fits the existing layout — current `tests/Unit/` only contains PHP component tests; the JS in `form.blade.php` has no harness yet)*

- [ ] **Step 1: Decide on a test approach**

Options, pick whichever matches the project's appetite:

1. **Workbench Dusk test** against the existing `workbench/` Laravel app — render a page with a form containing `<input type="radio" name="x" x-model="m">` inside a nested `x-data="{ m: 'a' }"`, assert that immediately after page load `Alpine.$data(form).isDirty` is `false`. This is the most faithful reproduction of the original bug.
2. **Plain headless JS test** (e.g. via Playwright with a static fixture HTML file). Lower setup cost than wiring up Dusk, but adds a JS-test dependency the package doesn't currently have.
3. **Skip automated test, document manual repro.** Acceptable if option 1 or 2 is too heavy for a one-line behavior change.

Recommend option 1 if the workbench is already wired for browser tests; otherwise option 3 + a note in the PR description.

- [ ] **Step 2: Cover the regression**

Whatever harness, the test should:
- Render a sleek form with `prevent-unload`.
- Inside it, render a nested `x-data` block containing two radios with `x-model` bound to a string the data block initializes — *without* an HTML `checked` attribute on either radio.
- Assert: immediately after Alpine starts, `formState.isDirty` (or the public `Alpine.$data(form).isDirty`) is `false`.
- Assert: after the user clicks the other radio, `formState.isDirty` becomes `true`.

---

### Task 3: Document the behavior

**Files:**
- Modify: `docs/forms.md`
- Modify: `docs/implicit-behaviors.md`

- [ ] **Step 1: Add a short section to `docs/forms.md` explaining dirty tracking**

Slot somewhere near the existing form documentation. Cover:
- That sleek forms emit `dirty` / `clean` events as users modify fields.
- That the baseline is captured one microtask after the form mounts, so Alpine `x-model` writes from nested `x-data` blocks are taken as the initial state (not as user edits).
- The `dirty="false"` opt-out attribute, with the existing `<input type="hidden" name="lang" dirty="false">` pattern as the canonical example. Use case: UI-only fields that happen to live inside the form element (e.g. mode-selector radios for "upload vs. select existing").
- The `prevent-unload` form-level attribute that gates the `beforeunload` warning on `isDirty`.

- [ ] **Step 2: Add an entry to `docs/implicit-behaviors.md`**

One-liner along the lines of "form dirty baseline is captured asynchronously (next microtask) so Alpine `x-model` writes during init aren't counted as edits."

---

### Task 4: Release

- [ ] **Step 1: Bump patch version in `package.json`**

This is a behavior fix, not a contract change. Bump the patch.

- [ ] **Step 2: Build and publish the package**

Use the existing release workflow.

- [ ] **Step 3: In the changelog / release notes, call out**:
- Bug: "Forms inside pages with nested Alpine `x-data` + `x-model` could report dirty on first load even with no user interaction. Now the dirty baseline is captured after Alpine has finished hydrating."
- The `dirty="false"` opt-out is unchanged and still recommended for non-form-data fields living inside form elements.

---

### Verification checklist (before merging)

- [ ] `online-systembrett` (or any consumer that hit the original bug) loads the video-offer edit page with no spurious dirty *even after removing the `dirty="false"` workaround on `preview_video_add_mode_*`*. Re-add the opt-out afterwards if you want to keep "this isn't real form data" semantics — both should yield clean state.
- [ ] User edits to genuine form fields (text input, Quill, file input, etc.) still flip `formDirty` to `true`.
- [ ] User undoing an edit (typing then deleting back to the original value) flips `formDirty` back to `false`.
- [ ] `beforeunload` still blocks navigation when there are real unsaved changes.
- [ ] Custom-element fields (`sleek-select`, `video-direct-upload`) still report dirty correctly when their internal value changes — the `upgrade` event path is unchanged.
