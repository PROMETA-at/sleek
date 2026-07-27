---
name: Allow custom field IDs for form-field label association
kind: bug
estimate: S
tags: [forms, accessibility]
---

Form-field labels derive the `for` attribute from the field *name* rather than an explicitly supplied ID. When the same form (or fields with the same names) appears multiple times on one page, `label[for]` / `input[id]` pairs collide and label association is broken for all but the first instance.

## Desired outcome

A field can be given an explicit ID through the straightforward component API (e.g. an `id` attribute on `x-sleek::form-field.*`), which is used for both the input's `id` and the label's `for`. The name-derived ID remains the default.

## Acceptance criteria

- Passing an `id` to a form-field component sets the input `id` and the matching label `for`.
- Default (no `id` passed) behavior is unchanged.
- Two instances of the same field name with distinct IDs produce valid, correctly associated markup.

## References

- `src/resources/views/components/form-field/` (input, select, textarea, checkbox, radio-group, wrapper)
- Source: consumer feedback, 2026-07-26 (repeated forms could not get correct label association).
