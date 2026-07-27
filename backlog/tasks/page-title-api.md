---
name: Per-page title API
kind: feature
estimate: S
tags: [layout, document]
---

There is no way to set a per-page document title. Both the document `<title>` (`src/resources/views/components/document.blade.php:7`) and the navbar brand (`navbar.blade.php:6-8`) read `env('APP_NAME')` directly.

## Desired outcome

A straightforward per-page title API — e.g. a `title` prop/slot on `x-sleek::document` / the page layout — rendering as "Page Title – App Name" by default. App name should come from `config('app.name')` rather than `env()` (env calls fail under config caching).

## Acceptance criteria

- A page can set its own document title through the component API.
- Default without a title remains the app name.
- `env('APP_NAME')` usages in views are replaced with `config('app.name')`.
- Documented in `docs/`.

## References

- Source: consumer feedback, 2026-07-26.
