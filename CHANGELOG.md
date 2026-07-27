# Changelog

## 1.1.0

### Added
- **Scoped-slot registry (`scopedSlots()`).** Components can register slot-name patterns with Sleek's Blade
  compiler so matching slots compile to closures instead of eager output — either zero-argument (deferred) or
  parameterized (`bind`-driven). Available to your own components. See `docs/directives.md`.

### Fixed
- **Inactive tab bodies no longer execute.** Tab slot bodies now run only for the active tab, matching the
  long-documented `$tab->content` contract. Aggregate queries and API calls in inactive tabs cost nothing until
  the tab is shown.
- **Missing `bind` on an entity-table column slot is now a compile-time error** naming the expected attribute
  (`bind="$value, $entity"`), instead of a runtime fatal deep in the row loop.
- Scoped slots with more than one attribute now compile correctly (the name/attributes split no longer breaks on
  commas inside the attribute array).
- **Self-closing tags can spread any variable.** `<x-icon {{ $spread }} />` matched no compiler pattern and was
  emitted as literal text — the raw tag rendered into the page. Spreading a variable other than `$attributes` now
  works on self-closing tags, as it always did on paired tags and slots.

### Changed
- **Side effects in inactive tab slot bodies** (counters, `@push`, `@once`) no longer run on initial load — they
  run when the tab is fetched, matching the HTMX fragment path. Audit any consumer that relied on an inactive
  tab's body running eagerly; none are expected.
- **`use=` on a scoped slot is obsolete and now ignored.** Every scoped slot captures its definition-site scope, so
  naming variables to carry in is no longer necessary. Existing templates keep compiling; delete the attribute at
  your leisure. Note that by-reference capture (`use="&$x"`) is gone with it — scope is captured by value.

### Upgrade note
- Run `php artisan view:clear` after upgrading — the compiled output of tab and entity-table slots changed.
