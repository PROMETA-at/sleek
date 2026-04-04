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
