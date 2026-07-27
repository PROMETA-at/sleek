---
name: Support non-default auth guards in navbar account section
kind: feature
estimate: S
tags: [auth, navbar]
---

The navbar account section uses `Auth::check()` with the default guard (`src/resources/views/components/navbar.blade.php:92`). Apps using a non-default guard (e.g. an `admin` guard) get wrong auth state and had to replace the account partial entirely.

## Desired outcome

The guard used by Sleek's auth-aware components is configurable (config key and/or component prop), defaulting to the app's default guard. The account partial should not need to be overridden just to change the guard.

## Acceptance criteria

- A configured guard (e.g. `admin`) is respected by the navbar account section for auth check and user display.
- Default behavior without configuration is unchanged.
- Documented in `docs/`.

## References

- Source: consumer feedback, 2026-07-26 (admin guard required a custom navbar account partial).
