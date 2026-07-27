# Blade Tag-Layer Lexer: Replace Regex Tag Parsing in Sleek's ComponentTagCompiler

> **For agentic workers:** Handoff for a fresh session. Read "Current state" and "Hard constraints"
> before touching code. Steps use checkbox (`- [x]`) syntax for tracking.
>
> **Related:** [`2026-07-07-scoped-slot-registry.md`](2026-07-07-scoped-slot-registry.md) — the scoped-slot
> feature whose regex implementation this plan replaces. Runtime side (closure slots, `Views\Factory`,
> `CallableComponentSlot`) is untouched by this plan.

**Goal:** Replace the regex passes inside `src/Blade/ComponentTagCompiler.php` with a single-pass
character lexer + tag tree + emitter. Output must be **byte-identical** to the current implementation.
Laravel's `BladeCompiler` keeps compiling everything else (directives, echoes, verbatim/php, layouts) —
this is NOT a wholesale Blade compiler replacement.

## Decision record (2026-07-26)

Evaluated three approaches; this plan is the survivor:

1. **Wholesale replacement via `stillat/blade-parser` — rejected on performance.** Structurally excellent
   (full AST, `isClosedBy`/`isOpenedBy` pairing, `isSlot()`, typed parameter nodes, `CustomComponentTagCompiler`
   contract) but its `DocumentParser` is quadratic in document size: 60 KB parses in ~214 ms, 967 KB in ~55 s;
   full compile of 625 KB takes ~87 s vs Laravel's 26 ms. A library compiling every consumer template cannot
   impose that tail. Benchmark + upstream investigation handoff: `~/scratch/blade-bench/`
   (`bench.php`, `scale.php`, `INVESTIGATION.md`). Revisit if upstream fixes the curve — its AST shapes remain
   the design reference here.
2. **Own full Blade compiler — rejected.** Whoever binds `blade.compiler` compiles every template in the
   consumer app; full-grammar parity (directive grammar, echo forms, `@extends` footers, ~25 `Compiles*`
   traits growing per release) is a permanent treadmill outside Sleek's domain.
3. **Hand-written tag-layer lexer — this plan.** O(n) by construction, zero new dependencies, no version
   constraint changes, replaces exactly the brittle part.

## Current state

Uncommitted changes on `master` implement the scoped-slot registry via regex passes. The parsing machinery
to replace, all in `src/Blade/ComponentTagCompiler.php`:

- `compileOpeningTags()` + `componentOpeningPattern()` / `componentSelfClosingPattern()` /
  `slotOpeningPattern()` — ~50-line regexes copied from Laravel and tweaked.
- `compileSlots()` — replays `slotOpeningPattern()` in `preg_replace_callback` while `array_shift`-ing
  attributions produced by a *separate* scan, relying on both regex executions visiting slot tags in
  identical order (the lock-step hazard motivating this plan).
- `attributeSlotsToComponents()` — five `preg_match_all` scans + offset sort + stack, reconstructing
  nesting that a tree gives for free.

**Keep unchanged:** `resolveSlotScoping()` and `matchScopedSlot()` (business logic, move as-is);
`BladeCompiler::compileSlot()`/`compileEndSlot()` and their emission contract
(`@slot($name, $attrs bind ($bindings))` scoped form, `@slot($name, null, $attrs)` eager form, ` @endslot`);
`scopedSlotRegistry()`; everything under `src/Views/`.

## Seam and input guarantees

`Illuminate\View\Compilers\BladeCompiler::compileString()` (verified against installed framework,
vendor `BladeCompiler.php:259`): `prepareStringsForCompilationUsing` callbacks → `storeUncompiledBlocks`
(masks `@verbatim`/`@php` blocks with placeholders) → `compileComponentTags(compileComments($value))`.

So the lexer's input has comments stripped and verbatim/php masked, but **raw `{{ }}` echoes still present**
(including inside attribute values and as spread attributes). Output goes on to directive/echo compilation.

## Hard constraints

- **Byte-identical output** to the current implementation on any input the current one handles. The
  differential harness (below) is the acceptance gate.
- **Tolerate unpaired tags.** Laravel compiles closing tags independently of pairing (every `</x-*>`
  emits its end-component output whether or not an open matched). The tree builder must NOT error on
  mismatches — best-effort stack, exactly like today's `attributeSlotsToComponents()`. The only
  compile-time errors are the ones that already exist (`resolveSlotScoping()` throws).
- Self-closing components never enter the nesting stack (no children, no slots).
- The parent class's semantic helpers stay the single source of truth for resolution and emission:
  `componentClass()`, `componentString()`, `getAttributesFromAttributeString()`, `attributesToString()`,
  `parseAttributeBag()`.

## Design

Three pieces in `src/Blade/`, wired into `ComponentTagCompiler::compile()`:

1. **Lexer** — one linear scan producing tokens: literal text spans + `ComponentOpen` / `ComponentSelfClose` /
   `ComponentClose` / `SlotOpen` / `SlotClose`. Tracks quote state (`"`, `'`), echo state (`{{ }}`), and
   paren depth for `@class(...)`/`@style(...)`. **Phase 1 keeps tag tokens carrying the raw attribute
   substring** and feeds it to the existing `getAttributesFromAttributeString()` — that's what makes
   byte-identity cheap. Typed attribute nodes are a later phase, only when a feature needs them.
2. **Tree builder** — pairs open/close tokens (tolerantly, see constraints) so each slot node's enclosing
   component is its parent. Replaces `attributeSlotsToComponents()` entirely.
3. **Emitter** — walks the tree in document order emitting via the parent helpers; slot nodes run the
   moved `resolveSlotScoping()` logic and emit the `@slot` forms above; unmatched constructs emit
   exactly what Laravel's independent passes would.

### Grammar checklist (what the lexer must recognize)

Derived from the current patterns — port behavior, don't re-derive:

- Tag names: `x-` or `x:` prefix, charset `[\w\-:.]` (dotted views, `sleek::` namespaces, `bs::` paths).
- Slot forms: `<x-slot:inlineName>` (kebab → camel), `<x-slot name="...">`, `<x-slot :name="...">`,
  inline name + name attribute combined (name becomes an attribute — see current `compileSlots()`),
  `</x-slot ...>` close (current close regex tolerates junk before `>`).
- Attribute kinds (Phase 1: recognized only to skip correctly, then handed off as raw string):
  static (bare, `="..."`, `='...'`, unquoted value), bound `:attr="expr"`, escaped `::attr`,
  shorthand `:$var`, spread `{{ $attributes... }}`, `@class([...])` / `@style([...])` with balanced
  parens, attribute-value echoes `{{ ... }}` inside quoted values. Name charset `[\w\-:.@%]`
  (components) vs `[\w\-:.@]` (slots).
- Tag-end guard: current patterns refuse a `>` immediately preceded by `/`, `=`, or `-`
  (`(?<![\/=\-])>`) — that's how `=>` and `->` inside unquoted bound expressions survive. The lexer's
  quote/echo tracking supersedes the trick but must not regress the cases it covered.
- Anything not forming a valid tag stays literal text (regex non-match today == lexer fallback to text).

## Testing

- [x] Differential harness: compile all package + workbench templates (77 files) through old and new
      pipelines, assert byte-identical output. Keep the old implementation available until this passes
      (e.g. compile via both classes in the test, not a runtime flag).
- [x] Port Laravel's `BladeComponentTagCompilerTest` fixtures (framework repo, `tests/View/Blade/`) as
      lexer edge-case inputs for the differential harness.
- [x] Existing tests stay green: `ScopedSlotCompilerTest`, `TabsLazyRenderTest`,
      `EntityTableColumnSlotTest`, `BtnComponentTest` (`composer test`).
- [x] Lexer unit tests for the cases regex is known to fumble: `>` inside quoted/unquoted bound values,
      `/>` inside strings, multiline attributes, `@class` with nested parens, unpaired tags.

## Steps

- [x] Lexer with token stream (Phase 1 raw-attribute-substring form) + unit tests.
- [x] Tree builder with tolerant pairing + slot-to-component attribution + unit tests.
- [x] Emitter reusing parent helpers; `resolveSlotScoping()`/`matchScopedSlot()` kept where they are.
      *Deviation:* emission stayed on `ComponentTagCompiler` (as `emit()` / `emitComponent()` /
      `emitSlot()`) rather than moving to a separate class — a standalone emitter would have had to
      widen the visibility of the parent's protected helpers to reach them.
- [x] Rewire `ComponentTagCompiler::compile()`; deleted `compileOpeningTags()`, `compileSlots()`,
      `attributeSlotsToComponents()`, and the three pattern methods. The pre-lexer implementation
      lives on as `tests/Fixtures/LegacyComponentTagCompiler.php`, the reference side of the harness.
- [x] Run full suite (140 tests green). `composer lint` could not run — `phpstan` is not installed
      in this checkout, and was already missing before this work.
- [x] `docs/implicit-behaviors.md` untouched: no documented convention changed.

## Outcome (2026-07-27)

Implemented as `src/Blade/TagLexer.php` (lexer), `src/Blade/TagToken.php`, `src/Blade/TagTree.php`
(pairing/attribution) and the `emit*()` methods on `src/Blade/ComponentTagCompiler.php`.

**Byte-identity holds** on all 77 shipped templates and the edge-case corpus
(`tests/Unit/TagLexerDifferentialTest.php`). Two deliberate differences, each covered by its own test:

1. **Self-closing tags can spread any variable.** Sleek had widened spread attributes from
   `{{ $attributes }}` to any `{{ $var }}` in the opening and slot patterns, but
   `componentSelfClosingPattern()` was still upstream Laravel's verbatim — so
   `<x-icon {{ $spread }} />` matched *no* pattern and fell through to the output as literal text,
   rendering the raw tag into the page. The lexer applies one spread rule to all three grammars.
   The harness caught this only because it was asked to: byte-identity locked the bug in until the
   asymmetry was questioned.
2. **Error reporting order.** With *several* unresolvable components in one template, the reported
   one changes — the regex pipeline compiled every self-closing tag before any opening tag, so the
   error came from whichever pass reached one first; emission is now in document order. Templates
   that compile at all are unaffected.

Scaling is linear, as intended: 21 KB / 1000 tags → 1.0 ms, 340 KB / 16 000 tags → 18.6 ms.

**Measured, not taken further:** fusing the three passes into generators / `LazyCollection` was
evaluated and rejected. Attribution + emission traversal together cost 0.77 ms of a 20.6 ms
pipeline (<4%), a fused generator was *slower* than array-build-plus-two-walks (1.98 vs 1.84 ms per
16 000 tokens), and `LazyCollection` cost 29.8 ms — it would more than double compile time.
Streaming would also force output to be emitted before a later tag can abort the compile.

The one real inefficiency left is in the tag matcher, not the iteration: every *opening* tag runs a
full failed self-closing DFS first, because that is the pass order the regexes ran in. Measured at
0.037 ms/KB for self-closing tags vs 0.074 ms/KB for opening ones — a 2× tax on the commonest tag
shape. Now that the spread rule is unified, `MODE_OPEN` and `MODE_SELF_CLOSE` differ *only* in their
terminator, so a single scan with a terminator predicate accepting either would collapse the two
without the re-validation step that would otherwise be needed. Not attempted here.

Known divergence not chased, because no real template can hit it: the regex passes ran
`compileSlots()` over the whole document first, so a slot tag buried inside another tag's quoted
attribute value (`<x-foo bar="<x-slot:a>">`) used to be compiled and mangle its host. The lexer
consumes the quoted value as a value and leaves it alone.
