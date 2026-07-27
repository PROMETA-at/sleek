---
name: Align sidebar and navbar collapse breakpoints
kind: bug
estimate: S
tags: [css, layout, responsive]
---

The sidebar/page layout collapses at a hardcoded 799px (`src/resources/views/components/page/index.blade.php:32`, `src/resources/views/components/navbar.blade.php:123,143`) while the Bootstrap navbar collapses at Bootstrap's `lg` breakpoint (992px). Between 799px and 992px the two are in inconsistent states.

## Desired outcome

Both collapse at the same breakpoint. Preferably derive from Bootstrap's breakpoint variables (`$grid-breakpoints` / `lg`) so a customized Bootstrap theme stays consistent, rather than a second hardcoded pixel value.

## Acceptance criteria

- Sidebar and navbar collapse/expand at the same width across resize.
- Breakpoint follows Bootstrap's configuration where feasible.

## References

- Source: consumer feedback, 2026-07-26 (799px vs Bootstrap lg).
