# Documentation Restructure Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Split the monolithic README into topic-focused docs, document undocumented components (modal, modal-form, tabs, alert, card, icon) and blade directives, and add an implicit-behaviors quick-reference.

**Architecture:** The current README (~1017 lines) gets slimmed to an elevator pitch + install + TOC linking into a `docs/` folder. Existing content moves as-is into topic files. New documentation for undocumented components and directives is added. An implicit-behaviors summary table ties it all together.

**Tech Stack:** Markdown documentation, no code changes.

---

### Task 1: Create new README.md (elevator pitch + install + TOC)

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Replace README.md with slim version**

```markdown
# Sleek Laravel Package

Sleek is a Laravel package that provides Bootstrap UI components with aggressive defaults for rapid Laravel development. Every behavior defaults to the most likely use case, while remaining fully customizable.

## Installation

To get started, install Sleek via the Composer package manager:

\`\`\`bash
composer require prometa/sleek
\`\`\`

By default, the service provider is automatically registered via [Laravel's package auto-discovery](https://laravel.com/docs/master/packages#package-discovery). No additional steps are required.

However, if auto-discovery is disabled or does not work as expected, you can register the service provider manually. To do this, add the following line to the `providers` array in your `config/app.php` file:

\`\`\`bash
\Prometa\Sleek\Providers\SleekServiceProvider::class,
\`\`\`

Sleek offers a setup command to automatically install and set up the necessary dependencies. The `sleek:setup` command will check your bootstrap and bootstrap-icons installations and inject an import to sleek's sass into your app.scss:

\`\`\`bash
php artisan sleek:setup
\`\`\`

## Documentation

- [Page Layout](docs/layout.md) — page scaffolding, assets, menu, authentication, language, theme
- [Navigation](docs/navigation.md) — breadcrumbs
- [Forms](docs/forms.md) — form, form fields, form groups, entity forms, value extraction
- [Tables](docs/tables.md) — entity tables, sorting, pagination, custom columns
- [Eloquent Extensions](docs/eloquent.md) — autoSort, autoPaginate, autoFilter
- [UI Components](docs/components.md) — icon, alert, card, modal, modal-form, tabs
- [Blade Directives](docs/directives.md) — @capture/@into, @flags/@flag, @forwardSlots, @ensureSlotFor
- [Implicit Behaviors](docs/implicit-behaviors.md) — quick-reference for all convention-based magic
```

- [ ] **Step 2: Commit**

```bash
git add README.md
git commit -m "docs: slim README to elevator pitch + install + TOC"
```

---

### Task 2: Split existing README — layout and navigation

**Files:**
- Create: `docs/layout.md`
- Create: `docs/navigation.md`

- [ ] **Step 1: Create `docs/layout.md`**

Move the following sections from the old README verbatim (they are well-written):
- "Page Layout" (opening paragraph + `sleek::view` example)
- "Defining Assets"
- "Defining the Menu Structure"
- "Authentication"
- "Language Switcher"
- "Theme Configuration"

Add a top-level heading `# Page Layout` and keep all sub-headings as-is.

- [ ] **Step 2: Create `docs/navigation.md`**

Move verbatim:
- "Navigation" > "Breadcrumbs"
- "Custom Breadcrumb Labels"

Add a top-level heading `# Navigation`.

- [ ] **Step 3: Commit**

```bash
git add docs/layout.md docs/navigation.md
git commit -m "docs: extract layout and navigation into docs/"
```

---

### Task 3: Split existing README — forms

**Files:**
- Create: `docs/forms.md`

- [ ] **Step 1: Create `docs/forms.md`**

Move verbatim:
- "Forms" (intro paragraph)
- "Defining a Form"
- "Defining Form Fields"
- "Field Names"
- "Field Labels" (including "Setting an Explicit Label" and "Setting a Custom Prefix")
- "Field values"
- "Grouping Form Fields" (including "Nesting Form Groups" and "A Note on Form Group Markup")
- "Entity Forms" (intro)
- "Form Method Guessing"
- "Form Action Guessing"
- "Form Field Generation"
- "Value extraction"
- "Using Form Groups in Entity-Forms"

Add a top-level heading `# Forms`.

- [ ] **Step 2: Commit**

```bash
git add docs/forms.md
git commit -m "docs: extract forms documentation into docs/"
```

---

### Task 4: Split existing README — tables

**Files:**
- Create: `docs/tables.md`

- [ ] **Step 1: Create `docs/tables.md`**

Move verbatim:
- "Entity Tables" (intro + example)
- "Table Header Generation"
- "Sorting Controls"
- "Pagination Controls"
- "Scoping Parameter Names"
- "Value Extraction" (table-specific)
- "Custom Columns"
- "Styling Columns"
- "Styling Rows"

Add a top-level heading `# Entity Tables`.

- [ ] **Step 2: Commit**

```bash
git add docs/tables.md
git commit -m "docs: extract tables documentation into docs/"
```

---

### Task 5: Split existing README — eloquent extensions

**Files:**
- Create: `docs/eloquent.md`

- [ ] **Step 1: Create `docs/eloquent.md`**

Move verbatim:
- "Eloquent Extensions" (intro)
- "Auto Sort"
- "Auto Paginate"
- "Auto Filter" (including "Filter Pipeline" and "Example Usage")

Add a top-level heading `# Eloquent Extensions`.

- [ ] **Step 2: Commit**

```bash
git add docs/eloquent.md
git commit -m "docs: extract eloquent extensions into docs/"
```

---

### Task 6: Document UI components

**Files:**
- Create: `docs/components.md`

- [ ] **Step 1: Create `docs/components.md` with all component documentation**

Write documentation for each component following the style and depth of the existing README sections (show the simple case first, then reveal customization options). Content for each component below:

```markdown
# UI Components

## Icons

Sleek provides a convenient icon component for Bootstrap Icons. Instead of writing
`<i class="bi bi-envelope"></i>`, use the `<x-icon />` component:

\`\`\`blade
{{-- Boolean attribute shorthand --}}
<x-icon envelope />
<x-icon person-fill />

{{-- Explicit name attribute --}}
<x-icon name="envelope" />

{{-- Additional attributes are forwarded to the <i> element --}}
<x-icon envelope class="text-primary" style="font-size: 1.5rem;" />
\`\`\`

## Alert

Sleek includes a toast-style alert system. If you use the Sleek layout (`sleek::view`),
the alert component is already included. Otherwise, add it to your layout:

\`\`\`blade
<x-sleek::alert />
\`\`\`

Trigger alerts from your controller using the Sleek facade:

\`\`\`php
use Prometa\Sleek\Facades\Sleek;

Sleek::raise('User created successfully', 'success');
Sleek::raise('Something went wrong', 'danger');
\`\`\`

The first parameter is the message, the second is the type. Supported types:
`primary`, `secondary`, `success`, `danger`, `warning`, `info`, `light`, `dark`.
Each type displays an appropriate icon automatically.

The alert auto-dismisses after 4 seconds with an animated progress bar that pauses
on hover.

### Alert Position

Configure the alert position in your `AppServiceProvider`:

\`\`\`php
Sleek::alert([
    'position' => 'top-right'
]);
\`\`\`

Available positions: `center`, `top-left`, `top-right`, `bottom`, `bottom-left`, `bottom-right`

## Card

A wrapper around Bootstrap's card component:

\`\`\`blade
<x-sleek::card>
    Card content goes here
</x-sleek::card>
\`\`\`

Cards support `header` and `footer` slots:

\`\`\`blade
<x-sleek::card>
    <x-slot:header>Card Title</x-slot:header>
    Card body content
    <x-slot:footer>Card footer</x-slot:footer>
</x-sleek::card>
\`\`\`

### Reactivity

Set `reactivity` to add a hover shadow effect:

\`\`\`blade
<x-sleek::card :reactivity="true">
    Hovering over this card adds a shadow transition.
</x-sleek::card>
\`\`\`

## Modal

Sleek provides a modal component that supports both Bootstrap modals and native HTML
`<dialog>` elements:

\`\`\`blade
{{-- Bootstrap modal (default) --}}
<x-sleek::modal id="my-modal">
    Modal content goes here
</x-sleek::modal>

{{-- Native HTML dialog --}}
<x-sleek::modal native>
    Dialog content goes here
</x-sleek::modal>
\`\`\`

### Header

\`\`\`blade
<x-sleek::modal id="my-modal">
    <x-slot:header>Modal Title</x-slot:header>
    Modal body content
</x-sleek::modal>
\`\`\`

When a header is provided, the close button is placed inside it. Otherwise, it floats
in the top-right of the body.

### Size

Control modal size with the `size` attribute:

\`\`\`blade
<x-sleek::modal size="lg" id="large-modal">...</x-sleek::modal>
<x-sleek::modal size="sm" id="small-modal">...</x-sleek::modal>
<x-sleek::modal size="xl" id="extra-large-modal">...</x-sleek::modal>
\`\`\`

### Flags

- `close` (default: `true`): Show/hide the close button. Use `noclose` to hide it.
- `native`: Render as HTML `<dialog>` instead of Bootstrap modal.
- `scroll` (default: `true`): On native dialogs, controls overflow behavior.

## Modal Form

A modal with an embedded form, including Alpine.js-powered loading state on submit:

\`\`\`blade
<button data-bs-target="#add-user-modal" data-bs-toggle="modal">Create User</button>

<x-sleek::modal-form
    title="Create User"
    :action="route('users.store')"
    method="POST"
    id="add-user-modal"
>
    <x-sleek::form-field type="text" name="name" />
    <x-sleek::form-field type="email" name="email" />
</x-sleek::modal-form>
\`\`\`

The form automatically includes CSRF protection and handles method spoofing, just like
`sleek::form`.

### Model Binding

Pass a model to pre-fill the form, just like `sleek::entity-form`:

\`\`\`blade
<x-sleek::modal-form
    title="Edit User"
    :model="$user"
    :fields="['name', 'email']"
    id="edit-user-modal"
/>
\`\`\`

When a model is provided, method and action guessing work the same as with
`sleek::entity-form`.

### Custom Buttons

Customize the cancel and submit button labels:

\`\`\`blade
<x-sleek::modal-form title="Confirm" :action="route('users.store')" id="my-modal">
    <x-slot:submit label="Save Changes" />
    <x-slot:cancel label="Discard" />
    ...
</x-sleek::modal-form>
\`\`\`

### Custom Header and Footer

Override the default header and footer entirely:

\`\`\`blade
<x-sleek::modal-form :action="route('users.store')" id="my-modal">
    <x-slot:header><h3>Custom Header</h3></x-slot:header>
    ...
    <x-slot:footer>
        <button type="submit">Go</button>
    </x-slot:footer>
</x-sleek::modal-form>
\`\`\`

### Native Dialog

Like `sleek::modal`, the modal-form supports the `native` flag for HTML `<dialog>`:

\`\`\`blade
<x-sleek::modal-form native title="Native Form" :action="route('users.store')" id="my-modal">
    ...
</x-sleek::modal-form>
\`\`\`

## Tabs

Sleek provides a tab component system with several styled presets and a headless base
component. Tabs use query string parameters for state, and integrate with HTMX for
partial page updates out of the box.

### Styled Presets

\`\`\`blade
<x-sleek::tabs.pills>
    <x-slot:tab-overview label="Overview">
        Overview content
    </x-slot:tab-overview>
    <x-slot:tab-settings label="Settings">
        Settings content
    </x-slot:tab-settings>
</x-sleek::tabs.pills>
\`\`\`

Available presets:
- `tabs.pills` — horizontal Bootstrap pills
- `tabs.vertical` — vertical pills on the left, content on the right
- `tabs.card` — Bootstrap card with navigation in the card header
- `tabs.collapse` — Bootstrap accordion

### How Tabs Work

- Tabs are identified by slot name (minus the `tab-` prefix). In the example above,
  the tabs are `overview` and `settings`.
- The active tab is determined by a query string parameter (default: `tab`). If not
  present, the first tab is shown.
- Tab buttons are regular links. Without JavaScript, they trigger a page reload.
- **With HTMX**, tabs load via AJAX automatically — no wiring needed. The component
  handles all necessary HTMX attributes and fragment rendering internally.

### Configuration

\`\`\`blade
{{-- Custom query parameter name --}}
<x-sleek::tabs.pills key-field="section">...</x-sleek::tabs.pills>

{{-- Custom default tab --}}
<x-sleek::tabs.pills default="settings">...</x-sleek::tabs.pills>

{{-- Accordion: flush style (no outer borders) --}}
<x-sleek::tabs.collapse flush>...</x-sleek::tabs.collapse>
\`\`\`

### Headless Component

When the presets don't fit, use the base component with your own markup:

\`\`\`blade
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
\`\`\`

Each `$tab` has:
- `$tab->link` — an `Htmlable` with an `ComponentAttributeBag` (the tab button/link)
- `$tab->content` — the tab's HTML content (only populated for the active tab)
- `$tab->active` — boolean
- `$tab->id`, `$tab->key` — identifiers

Both `$tab` and `$tab->link` support `withAttributes()` for fluent attribute modification.

> **Note:** The default slot uses a callable slot (via `bind`) to ensure it evaluates
> after all tab slots have been processed.

> **Note:** The tabs component overrides Laravel's fragment resolution internally.
> As such, tabs cannot be nested inside another fragment.
```

- [ ] **Step 2: Commit**

```bash
git add docs/components.md
git commit -m "docs: add UI components documentation (icon, alert, card, modal, modal-form, tabs)"
```

---

### Task 7: Document blade directives

**Files:**
- Create: `docs/directives.md`

- [ ] **Step 1: Create `docs/directives.md`**

```markdown
# Blade Directives

Sleek extends Laravel's Blade with several utility directives.

## @capture / @into

Capture a block of markup into a variable:

\`\`\`blade
@capture
    <p>Some content</p>
    <strong>More content</strong>
@into($myContent)

{{-- Use it later --}}
{{ $myContent }}
\`\`\`

The captured content is stored as a `ComponentSlot`, so it can be used anywhere a slot
is expected.

## @flags / @flag

Define boolean flag props on a component with automatic negated variants:

\`\`\`blade
{{-- In your component --}}
@flags(['close', 'native' => false, 'scroll'])
\`\`\`

This creates props for each flag and a negated `no`-prefixed variant:
- `close` (default: `true`) and `noclose`
- `native` (default: `false`) and `nonative`
- `scroll` (default: `true`) and `noscroll`

Use `@flag` for conditional rendering based on flag state:

\`\`\`blade
@flag('close')
    <button class="btn-close"></button>
@endflag

@unlessFlag('native')
    {{-- Bootstrap modal markup --}}
@endflag
\`\`\`

Consumers of the component can then use either the positive or negative form:

\`\`\`blade
{{-- These are equivalent --}}
<x-my-component noclose />
<x-my-component :close="false" />
\`\`\`

## @forwardSlots

Forward all named slots from the current component to a child component:

\`\`\`blade
{{-- In a wrapper component --}}
<x-inner-component>
    @forwardSlots
    {{ $slot }}
</x-inner-component>
\`\`\`

This passes through every named slot (except the default slot) with their attributes
preserved. Useful when building component wrappers that shouldn't need to know about
the inner component's slot interface.

## @ensureSlotFor

Guarantee a slot variable exists, avoiding `isset` checks:

\`\`\`blade
@ensureSlotFor($header)
@ensureSlotFor($footer, true)  {{-- force-create an empty ComponentSlot if missing --}}

@if($header->isNotEmpty())
    <div class="header">{{ $header }}</div>
@endif
\`\`\`

Without the second parameter, the slot is only normalized (strings become
`ComponentSlot` objects). With `true`, an empty `ComponentSlot` is created if the
variable doesn't exist at all.
```

- [ ] **Step 2: Commit**

```bash
git add docs/directives.md
git commit -m "docs: add blade directives documentation"
```

---

### Task 8: Create implicit behaviors quick-reference

**Files:**
- Create: `docs/implicit-behaviors.md`

- [ ] **Step 1: Create `docs/implicit-behaviors.md`**

```markdown
# Implicit Behaviors

Sleek's design principle is to aggressively default to the most likely use case. This
page summarizes every convention-based behavior — the things that happen without you
asking for them.

## Forms

| Behavior | Trigger | Rule | Override |
|---|---|---|---|
| Form method | `:model` present on `entity-form` | `PUT`; otherwise `POST` | `method="PATCH"` |
| Form action | Guessed method + current route name | `{route_prefix}.update` or `{route_prefix}.store` | `:action="route(...)"` |
| CSRF token | Method is not `GET` | Automatically injected | — |
| Method spoofing | Method is `PUT`, `PATCH`, or `DELETE` | `@method()` injected, form uses `POST` | — |
| Field label | Route name + field name | `{route_prefix}.fields.{name}` | `label="..."` or `i18nPrefix="..."` |
| Field value | `:model` + field name on ancestor form | Dot-notation into model attributes | `value="..."` or `accessor="..."` |
| Old input | Session flash data | Takes precedence over model value | — |
| Field name conversion | Dot-notation in `name` | `nested.name` becomes `nested[name]` | — |
| Multi-select suffix | `type="select" multiple` | Appends `[]` to name | — |
| Form group nesting | `form-group` with `name` | Prefixes all child field names | — |
| i18nPrefix inheritance | Set on form or form-group | All child fields inherit it | Per-field `i18nPrefix` |

## Tables

| Behavior | Trigger | Rule | Override |
|---|---|---|---|
| Column header i18n | Route name + column name | `{route_prefix}.fields.{first_dot_segment}` | `'col' => ['label' => __('...')]` |
| Value extraction | Column name + entity | Dot-notation into model attributes | `'col' => 'accessor'` or `'col' => ['accessor' => '...']` |
| Sorting controls | `:sortable="true"` | Adds `sort-by` and `sort-direction` query params | `:sortable="['col1', 'col2']"` |
| Pagination | `:entities` is a `Paginator` | Renders pagination links above and below | `navigation="top"`, `"bottom"`, or `:navigation="false"` |
| Page size links | Paginator present | Rendered alongside pagination | — |
| Parameter scoping | `scoped="prefix"` | All params become `prefix[param]` | — |

## Eloquent Extensions

| Behavior | Trigger | Rule |
|---|---|---|
| Auto sort | `->autoSort()` | Applies `orderBy` from `sort-by` and `sort-direction` request params |
| Auto paginate | `->autoPaginate($default)` | Applies `paginate()` from `page-size` request param (or `$default`) |
| Auto filter | `->autoFilter([...])` | Applies `where` clauses from request params matching configured fields |

## Alerts

| Behavior | Trigger | Rule |
|---|---|---|
| Icon selection | Alert type | Each type maps to a specific Bootstrap icon |
| Auto-dismiss | Alert rendered | Dismisses after 4 seconds; progress bar pauses on hover |

## Tabs

| Behavior | Trigger | Rule | Override |
|---|---|---|---|
| Active tab | Query string parameter | `?tab=key` selects active tab | `key-field="custom"` |
| Default tab | No query parameter | First tab is shown | `default="key"` |
| HTMX loading | HTMX available | Tabs load via AJAX with fragment rendering | — |

## Breadcrumbs

| Behavior | Trigger | Rule | Override |
|---|---|---|---|
| Segment labels | URL segments | `breadcrumbs.{segment}` translation key | — |
| Model labels | Route-bound model | Model's `asBreadcrumb()` method | Implement `RendersAsBreadcrumb` |

## Modal Form

| Behavior | Trigger | Rule |
|---|---|---|
| Loading spinner | Form submit | Submit button disabled, spinner shown (Alpine.js) |
| Method/action guessing | `:model` present | Same rules as `entity-form` |
```

- [ ] **Step 2: Commit**

```bash
git add docs/implicit-behaviors.md
git commit -m "docs: add implicit behaviors quick-reference"
```
