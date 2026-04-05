---
name: sleek-documentation-style
description: Use when writing or editing documentation prose in the Sleek Laravel package's docs/ folder or README.md — the writing phase after facts have been gathered
---

# Sleek Documentation Style

## Overview

Sleek documentation is a guided tour, not a reference manual. Every component and feature uses aggressive defaults — the docs need to showcase that magic, then explain it, then open it up for customization. The reader should feel like they're being walked through something awesome, not handed a spec sheet.

## When to Use

- Writing new documentation in `docs/*.md`
- Editing existing documentation in `docs/*.md`
- Editing the README
- Writing doc comments meant for end users (not internal code comments)

**Do NOT use for:**
- Internal code comments
- Memory/plan files
- Commit messages

## The Three-Part Structure

Every doc section follows this arc:

**1. Real use-case with aggressive defaults**

Open with a compact, realistic example that leans on as many defaults as possible. This immediately shows why the component is useful AND raises questions about what's happening implicitly — questions the next section answers.

**2. Explain the magic**

Because the example leans on defaults, walk through what happened behind the scenes. For each implicit behavior, show the default AND how to override it. This turns "what's going on?" into "I get it, and I'm in control."

**3. Enumerate permutations**

Dive deep into configuration options exhaustively. This is the reference section for when someone knows what they want and needs the knobs.

## Tone

Warm, conversational, guiding. Use phrases like:
- "automagically sets up a few things"
- "Lets break it down" / "Lets go through them one by one"
- "This is where it gets fun"
- "So you can do crazy stuff like the following"
- "That's it."

Avoid:
- Flat feature lists (bullet after bullet with no narrative)
- "The component supports: ..." style enumerations
- Reference-manual tone ("This prop does X. This prop does Y.")
- Hedging language ("might", "can be", "may")

## Canonical Pattern (Excerpt)

This excerpt from the Entity Forms section shows the full pattern in action:

```markdown
## Entity Forms

While the form and field components are already useful by themselves, Sleek also provides a `sleek::entity-form`
component, which can automatically build a form from a model!

\`\`\`blade
<x-sleek::entity-form
    :model="$user"
    :fields="[
        'name',
        'is_admin' => 'checkbox',
        'roles' => ['type' => 'select', 'multiple'],
    ]"
/>
\`\`\`

The above declaration automagically sets up a few things:

- Since we pass in a model the form assumes an update operation and sets the method to 'PUT'
- The form automatically assumes an "action" property based on the current route.
- The form creates form fields from the "fields" attribute.
- The form pulls out the field values from the given model.

Lets go through them one by one:

### Form Method Guessing

Depending on if you provide a model or not, the method of the form will be set to 'POST' or 'PUT' respectively.
...
```

Notice:
- Opens with an enthusiastic "can automatically build a form from a model!"
- Shows a rich example with multiple default-leveraging shortcuts
- Uses "automagically" and lists what happened
- Bridges with "Lets go through them one by one:"
- Each sub-section explains the default, then shows how to override

## Red Flags — Rewrite If You See These

- **Section opens with a list of props.** The example comes first, the props come after in context.
- **"The component supports X, Y, Z."** That's a reference manual. Show it doing X first.
- **No mention of what's implicit.** If the example uses defaults, the text must name them.
- **No override shown.** Every default-driven behavior needs an explicit escape hatch in the docs.
- **Paragraphs that could apply to any component.** Generic framing = nothing memorable.

## Common Mistakes

| Mistake | Fix |
|---|---|
| Listing all props up front | Show a real example, introduce props through it |
| Sterile technical tone | Add warmth — this is a tour, not a datasheet |
| Skipping "what's magic here" | Every default-leveraging example needs the breakdown |
| Showing magic without overrides | Always pair the default with its escape hatch |
| Hypothetical examples | Use realistic ones (users, products, orders — not `Foo` and `Bar`) |

## Quick Checklist

Before finalizing a doc section, verify:

- [ ] Opens with a realistic, compact example using defaults
- [ ] Example raises natural "what's happening?" questions
- [ ] Those questions are immediately answered in the next paragraph
- [ ] Each implicit behavior shows how to override it
- [ ] Tone is warm and conversational, not mechanical
- [ ] Permutations section exists for the knobs
