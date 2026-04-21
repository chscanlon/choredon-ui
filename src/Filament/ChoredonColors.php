<?php

declare(strict_types=1);

namespace Choredon\Ui\Filament;

use Filament\Support\Colors\Color;

/**
 * Registers Choredon's colour roles with Filament.
 *
 * Usage in a Panel Provider (smart-salon):
 *
 *   use Choredon\Ui\Filament\ChoredonColors;
 *
 *   public function panel(Panel $panel): Panel
 *   {
 *       return $panel
 *           ->colors(ChoredonColors::forPanel())
 *           // ... other panel configuration
 *           ;
 *   }
 *
 * What this does:
 *  - Generates Filament-compatible colour ramps from Choredon's palette hex values.
 *  - Maps Filament's six roles (primary, gray, danger, info, success, warning) onto
 *    Choredon's semantic palette.
 *  - Filament uses these ramps for backgrounds, borders, text, and interactive
 *    elements throughout the admin panel.
 *
 * What this does NOT do:
 *  - Typography. Set fonts via a custom Filament theme's theme.css file.
 *  - Surface colours (page background, card surfaces). Those need CSS overrides
 *    in the custom theme's theme.css. See filament-adapter.css in this package.
 *  - Component-level style tweaks. Use Filament's Blade view overrides or theme
 *    CSS for those.
 */
final class ChoredonColors
{
    /**
     * Filament's ->colors() method accepts an array keyed by role.
     * Each value is either a Filament Color constant (Color::Zinc) or
     * a Color::hex() ramp generated from a single hex value.
     *
     * @return array<string, array|string>
     */
    public static function forPanel(): array
    {
        return [
            // Primary: Triangle. Filament uses this for buttons, active states,
            // links, focus rings.
            'primary' => Color::hex('#1E8FB8'),

            // Gray: Choredon's warm neutral ramp. Rather than Tailwind zinc,
            // we give Filament a ramp anchored by Vellum at the light end
            // and ink-near-black at the dark end. This keeps Filament's
            // surfaces, borders, and muted text coherent with Choredon's
            // warm tonal direction instead of reading as cool/slate.
            'gray' => [
                50  => '#F5EFE3',
                100 => '#EAE2D3',
                200 => '#DDD3BF',
                300 => '#C7BFAE',
                400 => '#9E968A',
                500 => '#7A7267',
                600 => '#5A5347',
                700 => '#403A2F',
                800 => '#2B2721',
                900 => '#1A1815',
                950 => '#0F0E0C',
            ],

            // Danger: Underspot. The brand accent used sparingly for alerts.
            'danger' => Color::hex('#B8243C'),

            // Info: Deep. Secondary brand colour, used for informational notices.
            'info' => Color::hex('#0E5F82'),

            // Success: Muted green that sits tonally with Vellum.
            // Not a brand primitive: defined in tokens as feedback.success.
            'success' => Color::hex('#2E7D4F'),

            // Warning: Muted amber that sits tonally with Vellum.
            // Not a brand primitive: defined in tokens as feedback.warning.
            'warning' => Color::hex('#B37A1E'),
        ];
    }
}
