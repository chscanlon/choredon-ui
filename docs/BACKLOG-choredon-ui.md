# Backlog — choredon-ui

Deferred work for the Choredon design system package. Delete entries via commit when addressed.

Conventions:
- Every item has enough context to be actionable cold.
- App-specific issues belong in consumer-repo backlogs (`salon-central/BACKLOG.md`, eventually `smart-salon/BACKLOG.md`), not here — but note them here if they indicate an adapter gap.
- Items that belong to a future version (v0.2, v0.3) are grouped under their target version.

---

## v0.3 planned

The next minor version. Items here are already agreed to be in scope but not yet implemented.

### Dark theme

Add a complete dark palette — Wingbase as `surface-base`, Vellum as `text-primary`, with the warm-neutral ramp inverted and semantic tokens redefined. The current `.dark` block in the Flux adapter neutralises Flux's dark mode by re-asserting light values; v0.2 replaces that with a genuine dark theme that responds to `.dark` as intended.

This is the single biggest piece of work in v0.2. Involves:
- New palette values for dark mode (or derive from existing ramp)
- Semantic token redefinitions under `[data-theme="choredon"].dark`
- New Flux adapter section for dark mode
- New Filament theme values for dark mode
- Updating the reference page to include a theme toggle
- Verifying every existing component renders correctly in dark

### Further Blade composites

v0.2.0 introduced the Blade-components layer with `<x-choredon::date-nav>` (a generic app-UI composite). Future composites land the same way — either salon-domain or generic — as consumer needs surface them. Candidates from the original v0.2 sketch, still pending:

- `<x-choredon::appointment-card>` — customer-facing appointment summary
- `<x-choredon::stylist-roster>` — employee schedule grid snippet
- `<x-choredon::service-pill>` — compact service-type indicator

None are urgent — they only get built when a consumer actually needs them. Listed here to document the intended growth direction. See the policy-decisions section below for what earns a composite its place in the package.

### Reference app as a runnable Laravel app

Currently the reference is a single standalone HTML file at `docs/reference.html`. v0.2 should replace it with a small runnable Laravel app inside the package that renders tokens AND real Flux components AND Choredon Blade components under both themes. This becomes the live style guide and the authoritative test of whether the adapter is working.

### Icon set

The concept doc's geometric icon family (bookings, clients, revenue, done, schedule, payments) built as a consistent SVG set using the triangle-circle-rule vocabulary. Ships as individual SVGs under `brand/icons/` with a Blade helper for embedding.

---

## Adapter gaps (v0.1.x patches)

Issues where the current adapter doesn't fully handle a Flux or Filament scenario. Each should be small enough to ship as a v0.1.2, v0.1.3, etc.

### Flux password field `bg-white` issue

The original v0.1.0 install screenshot showed the password field rendering with a white background while the email field correctly picked up `surface-sunk`. In v0.1.1 this was addressed by adding `[data-flux-input]` selectors, and the agent using `<flux:input type="password" viewable>` in the login implementation. Current status: believed resolved but worth verifying directly under devtools next time salon-central is being looked at.

If the issue returns, the fix is to find the specific Flux selector being used and add it to the adapter's surface-override block.

### Feedback colour ramp stops beyond -500/-600

The Flux adapter currently only remaps Tailwind's `red-500`, `red-600`, `amber-500`, `green-600`, `blue-600`. Flux callouts, toasts, and badges likely use more stops (`-50` for backgrounds, `-800` for text, etc.), which currently fall through to Tailwind defaults. Worth filling in the full ramp mapping once a Flux callout or toast has been visually inspected.

- Trigger: first time a callout or toast is visible in a review
- File: `dist/flux-adapter.css`

### Filament adapter not yet installed into smart-salon

The Filament adapter has existed since v0.1.0 but hasn't been tested in a real smart-salon environment. Until it is, its correctness is theoretical. First smart-salon install will surface gaps.

- Trigger: smart-salon install task begins

---

## Nice-to-haves (not v0.2, possibly later)

### `<x-choredon::date-nav>` `:label-formatter` closure prop

Allow consumers to pass a closure that receives the current date + granularity and returns a custom label string, overriding the built-in `DateNavState::shortLabel()` output. Deferred in v0.2.0 on the basis that the built-in labels (`Mon 20 Apr 2026`, `20 – 26 Apr 2026`, `April 2026`) cover current consumer needs and no custom-formatting use case has surfaced. Design note: keep the verbose (screen-reader) label computed by DateNavState regardless — consumers override the visible label only.

### `<x-choredon::date-nav>` `:dispatch-event` opt-in

Allow consumers to pass an event name (e.g. `:dispatch-event="roster-week-changed"`) that the component dispatches via Livewire whenever `currentDate` changes from a nav interaction. Deferred in v0.2.0 because `wire:model` + the parent's `updated<Property>()` hook covers every current consumer need. Only add if a real requirement emerges (e.g., sibling Livewire components on the same page that need to react without being tightly coupled to the roster component's state).

### Wordmark with circle-triangle 'o' treatment

The concept doc shows the "choredon" wordmark with 'o' characters rendered as circles containing small triangles, reinforcing the brand's geometric vocabulary in the wordmark itself. Current `wordmark.svg` uses live Fraunces text, which is accessible and flexible but doesn't have the circle-triangle detail.

A path-based variant would look more distinctive but lose the accessibility of live text. Worth building as an alternate asset for hero/editorial use where the extra craft is visible, while keeping the live-text version for UI contexts.

- File: `brand/logos/wordmark-with-glyphs.svg` (new)

### Density tokens for data-dense views

Admin and employee-facing surfaces with lots of data (Filament resource tables, schedule grids, customer lists) would benefit from a density-mode system. A `--density: compact | comfortable | spacious` token with components responding to it. Not urgent — current Flux/Filament defaults work fine. Worth considering if users request denser views.

### Seasonal butterfly variants

The brand butterfly could have restrained seasonal palette variants — autumn tones, Mother's Day pink hindwing accents, etc. Not identity changes, just colourway swaps for specific marketing moments. Low priority; relevant only if a marketing calendar emerges.

### Print stylesheet

Choredon's aesthetic is print-friendly by design (serif display, warm neutrals, restrained colour), but there's no dedicated print stylesheet. If invoices, receipts, or printed schedules become a feature, add `dist/choredon-print.css` that optimises for paper output (pure Vellum becomes pure white, ink becomes pure black for best printer reproduction, no backgrounds, etc).

### Single-colour logo variants

For contexts like embroidery, vehicle signage, single-ink printing, or places where the full palette can't render, build single-colour variants of the lockup and mark: all-black, all-white, Triangle-blue-only. These are straightforward but don't yet exist.

- Files: `brand/logos/mark-mono.svg`, `brand/logos/horizontal-mono.svg`

---

## Policy decisions (recorded, not tasks)

Decisions that have been made and documented here so the reasoning isn't lost.

### Gray-* ramp deliberately not remapped

Tailwind's `gray-*` ramp is left at Tailwind defaults rather than being mapped onto Choredon's warm neutral. This is intentional: any `gray-*` usage in consumer views represents legacy or ad-hoc styling that should be found and migrated or removed in a code audit, not silently corrected by the adapter. Do not add a `gray-*` remap without a specific reason to reverse this decision.

### Semantic tokens only for components

Blade components and adapter CSS should reference semantic tokens (`--color-interactive-primary`, `--color-surface-base`) or component-layer tokens — never palette tokens directly (`--palette-triangle`, `--palette-vellum`). Palette tokens are theme-private.

### Blade-components layer covers both salon-domain AND generic app composites

Established with v0.2.0. The original v0.1 framing said Blade components are "only for domain-specific composites (e.g., appointment cards, stylist rosters) that Flux doesn't provide." When `<x-choredon::date-nav>` was being planned, this framing was revisited: date navigation is not a salon concept, but it *is* a composite that wraps multiple Flux primitives (button + dropdown + popover + calendar) with shared behavior (prev/next math, period snapping, today state, WCAG 2.1 AA annotations) — and having every consuming app re-implement it defeats the design-system goal.

The layer's policy is now: composites earn their place when (a) they'd otherwise be duplicated across more than one page or more than one consuming app, and (b) they're non-trivial enough that re-implementation would produce inconsistency. Either salon-domain or generic app-UI qualifies. When in doubt, prefer using Flux directly.

### currentColor for structural logo elements

The butterfly uses `currentColor` for wing outlines, body, head, and antennae so it adapts to the parent's text colour (Vellum on Wingbase, Ink on Vellum). The mark, horizontal lockup, and wordmark use hardcoded palette values because they're designed for specific contexts.

---

## Upcoming consumer-side work (for visibility)

Not design-system backlog, but useful to see what's coming that will drive adapter iterations:

- Login page implementation (salon-central) — complete
- Browser spot-check authenticated Flux components in salon-central
- Install into smart-salon (Filament + employee Volt/Livewire)
- First external salon onboarding (informs theming architecture)
