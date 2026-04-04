# Modals

Sleek gives you a `sleek::modal` component that wraps Bootstrap modals (or native HTML `<dialog>` elements) with
sensible defaults, and a `sleek::modal-form` component that combines a modal with a full form — complete with model
binding, method guessing, and a loading spinner. Lets start with the basics.

## The Modal Component

At its simplest, a modal is just a container for content:

```blade
<x-sleek::modal id="confirm-modal">
    <x-slot:header>Are you sure?</x-slot:header>
    <p>This action cannot be undone.</p>
</x-sleek::modal>
```

Out of the box, this gives you a centered, fade-in Bootstrap modal with a close button in the header. If you skip the
header entirely, the close button floats to the top-right of the body instead — so you always get a way to dismiss
the modal without any extra work.

### Header

The `header` slot controls the top of the modal. When present, the close button sits neatly inside it:

```blade
<x-sleek::modal id="my-modal">
    <x-slot:header>Modal Title</x-slot:header>
    Body content here
</x-sleek::modal>
```

Leave it out, and the close button moves to the top-right corner of the body. Either way, your users can always
close the modal.

### Size

Control the modal width with the `size` attribute — the same sizes Bootstrap gives you:

```blade
<x-sleek::modal size="sm" id="small-modal">...</x-sleek::modal>
<x-sleek::modal size="lg" id="large-modal">...</x-sleek::modal>
<x-sleek::modal size="xl" id="extra-large-modal">...</x-sleek::modal>
```

### Flags

The modal supports a handful of boolean flags to tweak behavior:

- **`close`** (default: `true`) — Renders the close button. Use `noclose` to hide it when you want to force the user
  through a specific flow.
- **`native`** — Swaps the Bootstrap modal for a native HTML `<dialog>` element (more on that below).
- **`scroll`** (default: `true`) — On native dialogs, controls overflow behavior. Set `noscroll` to allow content to
  overflow visibly (useful for dropdowns or datepickers inside the dialog).

### Native HTML Dialog

If you prefer the platform's native `<dialog>` over Bootstrap's JS-driven modal, just add the `native` flag:

```blade
<x-sleek::modal native id="native-modal">
    <x-slot:header>Native Dialog</x-slot:header>
    This renders as a &lt;dialog&gt; element with Bootstrap styling applied.
</x-sleek::modal>
```

You get the same slots, the same close button behavior, and the same size options — just backed by the browser's
built-in dialog mechanics instead of Bootstrap JavaScript.

> **Important:** Since native dialogs don't use Bootstrap's JavaScript, `data-bs-toggle="modal"` won't work to open
> them. You'll need to open native dialogs through the `<dialog>` element's JS interface
> (e.g., `document.getElementById('native-modal').showModal()`).

### Extra Slot

The `extra` slot lets you inject additional content after the modal body. This is how `sleek::modal-form` places its
form element, but you can use it for anything that needs to live outside the body container:

```blade
<x-sleek::modal id="my-modal">
    <x-slot:header>Title</x-slot:header>
    Body content
    <x-slot:extra>
        <div class="modal-footer">Custom footer content</div>
    </x-slot:extra>
</x-sleek::modal>
```

All additional slots are forwarded to the inner Bootstrap modal via `@forwardSlots`, so you have full access to
Bootstrap's modal sub-components.

---

## Modal Form

This is where it gets fun. The `sleek::modal-form` component combines a modal with a full form — and it brings along
all the magic from `sleek::entity-form`. Check this out:

```blade
<a data-bs-target="#edit-user-modal" data-bs-toggle="modal">Edit User</a>

<x-sleek::modal-form
    title="Edit User"
    :model="$user"
    :fields="['name', 'email' => 'email', 'role' => 'select']"
    id="edit-user-modal"
/>
```

That's it. A fully functional edit modal with a form, pre-filled fields, cancel and submit buttons, and a loading
spinner. Lets break it down — here's what happened automagically:

- Since we passed a **model**, the form method was set to `PUT`
- The form **action** was guessed from the current route (e.g., `users.update`)
- The **fields** were generated as form fields, with values pulled from `$user`
- A **header** was created from the `title` prop
- **Cancel** and **Submit** buttons were rendered in the footer with translated labels
- The submit button gets **disabled with a spinner** on form submission via Alpine.js

Lets go through the important parts:

### Model Binding

Just like `sleek::entity-form`, passing a `:model` triggers the full suite of smart defaults. Method guessing, action
guessing, and value extraction all work exactly the same way:

```blade
{{-- No model: method defaults to POST, action guesses <prefix>.store --}}
<x-sleek::modal-form
    title="Create User"
    :fields="['name', 'email']"
    id="create-user-modal"
/>

{{-- With model: method becomes PUT, action guesses <prefix>.update --}}
<x-sleek::modal-form
    title="Edit User"
    :model="$user"
    :fields="['name', 'email']"
    id="edit-user-modal"
/>
```

You can always override the guessed values explicitly:

```blade
<x-sleek::modal-form
    title="Edit User"
    :model="$user"
    method="PATCH"
    :action="route('admin.users.update', $user)"
    :fields="['name', 'email']"
    id="edit-user-modal"
/>
```

### Fields and Custom Body Content

The `fields` attribute works identically to `sleek::entity-form` — shorthand names, `name => type` pairs, or verbose
arrays with extra options:

```blade
<x-sleek::modal-form
    title="Create User"
    :fields="[
        'name',
        'email' => 'email',
        'role' => [
            'type' => 'select',
            'options' => $roles,
        ],
    ]"
    id="create-user-modal"
/>
```

For more complex layouts, skip the `fields` attribute and use the default slot (or the `body` slot) directly:

```blade
<x-sleek::modal-form title="Create User" :action="route('users.store')" id="create-user-modal">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
        <x-sleek::form-field name="first_name" />
        <x-sleek::form-field name="last_name" />
    </div>
    <x-sleek::form-field name="email" type="email" />
</x-sleek::modal-form>
```

### Loading State

When the form is submitted, Alpine.js kicks in: the submit button becomes disabled and a spinner appears next to the
label. This happens automatically — no configuration needed. The spinner is hidden by default and only activates once
Alpine.js has initialized, so there is no flash of spinner on page load.

### Button Labels

The footer buttons pull their labels from the `common.actions.cancel` and `common.actions.submit` translation keys by
default. To override them, use the `cancel` and `submit` slots with a `label` attribute:

```blade
<x-sleek::modal-form title="Confirm Deletion" :action="route('users.destroy', $user)" method="DELETE" id="delete-modal">
    <p>Are you sure you want to delete this user?</p>
    <x-slot:cancel label="Keep User" />
    <x-slot:submit label="Yes, Delete" />
</x-sleek::modal-form>
```

### Custom Header and Footer

Need full control? Override the `header` and `footer` slots entirely:

```blade
<x-sleek::modal-form :action="route('users.store')" title="Create User" id="create-modal">
    <x-slot:header>
        <h5 class="modal-title">Custom Header Content</h5>
    </x-slot:header>

    <x-sleek::form-field name="name" />

    <x-slot:footer>
        <a data-bs-dismiss="modal">Never mind</a>
        <button type="submit" class="btn btn-primary">Create</button>
    </x-slot:footer>
</x-sleek::modal-form>
```

When you override the footer, you take full responsibility for the submit button and dismiss behavior — the loading
state and default buttons are replaced by whatever you provide.

### Custom Form Component

By default, `sleek::modal-form` uses `sleek::form` as its inner form component. If you have a custom form component
that you would like to use instead, pass it via the `formType` prop:

```blade
<x-sleek::modal-form
    title="Special Form"
    formType="my-custom-form"
    :action="route('users.store')"
    id="special-modal"
/>
```

This renders `<x-sleek::my-custom-form>` inside the modal instead of `<x-sleek::form>`.

### Native Dialog

Just like the base modal, `sleek::modal-form` supports the `native` flag for HTML `<dialog>`:

```blade
<x-sleek::modal-form native title="Create User" :action="route('users.store')" id="native-form-modal">
    <x-sleek::form-field name="name" />
    <x-sleek::form-field name="email" type="email" />
</x-sleek::modal-form>
```

When using native dialogs, the cancel button uses Hyperscript (`_="on click close() the closest <dialog />"`) instead
of Bootstrap's `data-bs-dismiss="modal"` to close the dialog.
