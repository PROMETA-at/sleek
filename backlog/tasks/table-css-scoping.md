---
name: Scope responsive table CSS and make breakpoint configurable
kind: task
estimate: M
tags: [css, entity-table, responsive]
---

Sleek's responsive table CSS is emitted globally and hardcodes a 600px breakpoint (`src/resources/views/components/entity-table.blade.php:59`, `src/resources/views/pagination.blade.php:106`). It affects tables outside Sleek components and cannot be tuned per app.

## Desired outcome

- The responsive rules are scoped to Sleek's own table markup (a dedicated class) instead of applying globally.
- The breakpoint is customizable (Sass variable and/or CSS custom property), defaulting to the current 600px.

## Acceptance criteria

- Non-Sleek tables on the same page are unaffected by Sleek's responsive CSS.
- Consumers can override the breakpoint without forking views.
- Documented in `docs/`.

## References

- Source: consumer feedback, 2026-07-26.
