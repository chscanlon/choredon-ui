# Choredon UI Changelog

## v0.2.0 — Filament adapter rewrite + first Blade components

Two independent pieces of work land in this release: the Filament adapter is rewritten for v4/v5, and the package grows a Blade-components layer with `<x-choredon::date-nav>` as its first tenant.

### Added — Blade components layer

- **`Choredon\Ui\UiServiceProvider`** — new Laravel service provider, auto-discovered via `extra.laravel.providers` in `composer.json`. Registers the `choredon` view namespace (from `resources/views/`) and the `choredon` Blade component namespace (from `Choredon\Ui\Components\`). Consumer apps now get `<x-choredon::…>` component resolution without manual wiring.
- **`<x-choredon::date-nav>`** — reusable date navigation (prev / label / today / next) for week-granular, month-granular, and day-granular contexts. Composes `flux:button`, `flux:dropdown`, `flux:popover`, and `flux:calendar`. Emits Y-m-d strings via `wire:model`. Targets WCAG 2.1 AA (role=group, verbose aria-labels, aria-live period announcements, 44px touch targets, focus return on picker close). Timezone-agnostic; consumers supply `:today` in their business timezone.
- **`Choredon\Ui\Components\Support\DateNavState`** — pure-PHP date-math helper. Unit-tested (28 tests, 54 assertions) without booting Laravel; only depends on Carbon (transitively via `illuminate/support`).
- **Policy clarification (README + BACKLOG):** the Blade-components layer now covers *composites* in two categories — (1) salon-domain composites that Flux doesn't provide (`appointment-card`, `stylist-roster`, etc., still pending), and (2) generic app-UI composites that wrap multiple Flux primitives with shared behavior (date navigation is the first of these). Previously the README implied category 2 was out of scope.
- **Pest test harness.** `tests/Unit/` with `pestphp/pest` as a dev dep. Unit tests run without any Laravel boot — they exercise pure PHP classes that don't depend on the framework.

### Changed — Filament adapter rewrite for v4/v5

Scoped update to prepare for installing Choredon into smart-salon (Filament v5.3 on Tailwind v4). The v0.1 Filament adapter was written for Filament v3 on Tailwind v3 and would not apply correctly to a v4/v5 panel.

- **Filament adapter rewritten for v4/v5 on Tailwind v4.** New `dist/filament-adapter.css` targets current Filament class hooks and uses Tailwind v4 CSS-first configuration (`@theme` blocks instead of tailwind.config.js). The v0.1 adapter is preserved alongside as `filament-adapter.css.v01-backup` for reference; do not import it.
- **Primary button text colour made explicit.** Filament v4 introduced automatic contrast-based text colouring that can produce dark text on medium-dark backgrounds. The adapter now forces `--color-text-on-primary` (pale Vellum) on `.fi-btn.fi-color-primary` regardless of the contrast picker.
- **Border radius tokens aligned with Choredon's crisp scale.** Filament's Tailwind radii now read from Choredon's 2/4/6/8/12/16px scale via `@theme`, replacing Filament's softer defaults.
- **Table treatment specified.** Explicit rules for `.fi-ta-*` hooks to ensure Vellum surface and warm-neutral dividers rather than the default white/zinc.
- **Form input selectors broadened.** Added `.fi-fo-text-input`, `.fi-fo-textarea-input`, `.fi-fo-select-input` alongside the existing `.fi-input` selectors. Filament v4+ uses the more-specific hooks in several form field types.

### Unchanged

- `ChoredonColors::forPanel()` PHP helper — the `->colors([...])` panel API is stable across v3/v4/v5.
- Flux adapter — already Tailwind-v4-compatible (salon-central has been running v0.1.1 against Tailwind v4.0.7 since April without issues).
- Tokens, palette, semantic layer — all stable.

### Known limitations (carried over)

- No dark theme. Still deferred to v0.3.
- Third-party Filament plugins' own views (in smart-salon's case `achyutn/filament-log-viewer` and `croustibat/filament-jobs-monitor`) may not honour the theme. Budget visual-QA time when those plugins are in use.
- Some Filament component hook classes have not been verified against the live v5.3 markup — the adapter uses well-documented hooks but rarer ones may need tweaking during install.

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
