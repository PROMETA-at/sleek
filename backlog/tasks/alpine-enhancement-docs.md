---
name: Document Alpine as optional progressive enhancement for forms
kind: task
estimate: XS
tags: [forms, docs, javascript]
---

`x-sleek::form` and `x-sleek::modal-form` emit Alpine directives (`x-data="sleek__form"`, `x-bind`, `alpine:init` listeners) but Sleek deliberately does not ship Alpine. This is intended progressive enhancement: without Alpine the bindings are inert and native submission works; installing Alpine activates the dirty-state and loading enhancements (confirmed by stakeholder, 2026-07-26 — by design, not a bug).

A consumer interpreted the inactive enhancements as a defect, so the design needs to be documented.

## Desired outcome

`docs/` explains that Alpine is an optional peer: what works without it, what installing it enables (dirty/loading behavior), and how to add it.

## Acceptance criteria

- Form docs state the progressive-enhancement design and list the Alpine-powered behaviors.
- Includes a short "enable enhancements" snippet (installing/registering Alpine).
- Follows the `sleek-documentation-style` conventions.

## References

- `src/resources/views/components/form.blade.php`, `src/resources/views/components/modal-form.blade.php`
- Source: consumer feedback, 2026-07-26.
