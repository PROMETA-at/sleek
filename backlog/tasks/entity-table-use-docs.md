---
name: Document the entity-table scoped-slot `use` attribute
kind: task
estimate: XS
tags: [entity-table, docs]
---

Entity-table scoped slots accept a `use` attribute, mirroring PHP's `use` keyword, to pass caller-scope values into the slot closure. This is undocumented: a consumer, unaware of it, attached temporary presentation attributes (`is_current`, `tenant_slug`) to their models to smuggle loop/caller state into publication rows.

This is a documentation gap, not a functional limitation (confirmed by stakeholder, 2026-07-26).

## Desired outcome

The `use` attribute is documented in `docs/` with a realistic example — passing loop/caller state (e.g. a current-item flag) into a scoped slot — so consumers don't resort to fake model attributes.

## Acceptance criteria

- `docs/` covers the `use` attribute on entity-table scoped slots with a working example.
- Follows the `sleek-documentation-style` conventions.

## References

- `src/resources/views/components/entity-table.blade.php`
- Source: consumer feedback, 2026-07-26.
