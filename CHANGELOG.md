# Choredon UI Changelog

## v0.1.1 — Flux adapter refinement

First adapter iteration based on the v0.1.0 install into salon-central.

### Fixed

- **Headings now render in Fraunces.** Added `[data-flux-heading]` selector binding `font-family: var(--font-display)`. Previously, `<flux:heading>` rendered in Inter Tight because Flux's default is `font-sans` and the adapter only defined `--font-serif` without binding it to any component.
- **Dark mode neutralised.** A `:root.dark` / `html.dark` block re-applies Choredon's essential overrides so Flux's dark mode produces the light theme regardless of the `.dark` class. Users toggling dark mode will see "nothing changes" rather than a broken interface. Proper dark theme arrives in v0.2.
- **Broader surface overrides.** Added explicit rules for `[data-flux-card]`, `[data-flux-modal]`, `[data-flux-dropdown-menu]`, `[data-flux-popover]`, `[data-flux-autocomplete-items]`, `[data-flux-toast]` to catch components that bypass the `.bg-white` utility.
- **Input surface treatment.** Flux inputs, selects, and textareas now use Choredon's `surface-sunk` for a subtle pressed-in feel, consistent with the Filament adapter.

### Deliberately not addressed

- **Tailwind's `gray-*` ramp is NOT remapped.** The v0.1.0 install report noted that handwritten views in salon-central use `gray-*` classes which read as cool-slate against Choredon's warm surfaces. Rather than mask this via the adapter, `gray-*` is left at Tailwind defaults. Any remaining use in salon-central should be found by code audit and either deleted (if the view is superseded) or migrated to `zinc-*` (if it's legitimate). The adapter's job is to theme Flux, not to hide ad-hoc colour choices in consumer code.

### Unchanged

- Token structure, palette, semantic layer — all stable.
- `choredon.css` — no changes to the compiled token file.
- Filament adapter — unchanged; this release is Flux-only.

### Known limitations (carried over from v0.1.0)

- No dark theme. v0.1.1 neutralises Flux's dark mode rather than implementing a Choredon dark palette; v0.2 will add real dark-mode support.
- Feedback colour ramps (red, amber, green, blue) are remapped only at their most-used stops (`-500`, `-600`). Flux callouts may use `-50` for backgrounds and `-800` for text; those fall through to Tailwind defaults. Will be revisited once a callout is visually inspected.
- No hand-rolled Blade components yet; Flux Pro covers current salon-central needs.

## v0.1.0 — Initial release

- Three-layer token architecture (palette / semantic / component).
- Warm-neutral ramp generated for framework-adapter consumption.
- CSS compiler (PHP) resolving `{path.to.token}` references.
- Tailwind preset generator.
- Flux UI adapter (zinc remap, accent, fonts, radii, feedback colours, `.bg-white` utility override).
- Filament adapter: `ChoredonColors::forPanel()` PHP helper + theme CSS for typography, sidebar, topbar, sections, inputs.
- Standalone reference page rendering every token.
