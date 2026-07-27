---
name: Make /lang/{locale} route opt-in
kind: bug
estimate: S
tags: [routing, i18n]
---

Sleek unconditionally registers the public `GET /lang/{locale}` route (`src/routes/web.php:7`) and appends `LocaleMiddleware` to the `web` group (`src/Providers/SleekServiceProvider.php:212`), even when the consuming app has no language switcher. Apps get an unexpected public endpoint that mutates session state.

## Desired outcome

The locale route (and ideally the middleware) is only registered when locale switching is actually configured/enabled — e.g. gated on a config flag or on locales being defined — while remaining on by default only if that matches Sleek's "aggressive defaults" principle without surprising consumers. Decide and document the default.

## Acceptance criteria

- A consuming app can disable (or must enable) the `/lang/{locale}` route via configuration.
- Behavior is documented in `docs/`.
- Existing apps that use the navbar language switcher keep working.

## Open questions

- Should the route also validate the locale against a configured allowlist? (Currently any string is stored in the session.)

## References

- `src/routes/web.php`, `src/Middleware/LocaleMiddleware.php`, `src/Providers/SleekServiceProvider.php:212`, navbar switcher at `src/resources/views/components/navbar.blade.php:82`
- Source: consumer feedback, 2026-07-26.
