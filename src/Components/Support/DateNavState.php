<?php

declare(strict_types=1);

namespace Choredon\Ui\Components\Support;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * Pure-PHP date-math state for the DateNav Blade component.
 *
 * The component accepts and emits Y-m-d strings at all granularities.
 * Prev/next advance by one unit of granularity (1 day, 7 days, 1 month),
 * preserving the day-of-week (week) or day-of-month (month) where sensible.
 * The parent Livewire component decides what it means to "be in" the period
 * — for week granularity it typically does Carbon::startOfWeek(MONDAY) to
 * derive the containing week.
 *
 * This class is unit-testable without Laravel — it only depends on Carbon.
 */
final class DateNavState
{
    public readonly CarbonImmutable $current;

    public readonly CarbonImmutable $today;

    public readonly ?CarbonImmutable $min;

    public readonly ?CarbonImmutable $max;

    public function __construct(
        string $currentDate,
        public readonly Granularity $granularity,
        ?string $today = null,
        ?string $min = null,
        ?string $max = null,
    ) {
        $this->current = $this->parse($currentDate, 'currentDate');
        $this->today = $today !== null ? $this->parse($today, 'today') : CarbonImmutable::today();
        $this->min = $min !== null ? $this->parse($min, 'min') : null;
        $this->max = $max !== null ? $this->parse($max, 'max') : null;

        if ($this->min !== null && $this->max !== null && $this->min->gt($this->max)) {
            throw new InvalidArgumentException('min must be on or before max');
        }
    }

    public function previousDate(): string
    {
        return $this->step(-1)->toDateString();
    }

    public function nextDate(): string
    {
        return $this->step(1)->toDateString();
    }

    public function canGoPrevious(): bool
    {
        $candidate = $this->step(-1);

        return $this->min === null || $candidate->gte($this->min);
    }

    public function canGoNext(): bool
    {
        $candidate = $this->step(1);

        return $this->max === null || $candidate->lte($this->max);
    }

    public function isToday(): bool
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->isSameDay($this->today),
            Granularity::Week => $this->current->startOfWeek(CarbonImmutable::MONDAY)
                ->isSameDay($this->today->startOfWeek(CarbonImmutable::MONDAY)),
            Granularity::Month => $this->current->format('Y-m') === $this->today->format('Y-m'),
        };
    }

    /**
     * Whether the Today button is actionable. False when we're already there,
     * and also false when "today" falls outside the min/max range — consumers
     * that limit the nav (e.g. completed weeks only) get the Today button
     * disabled rather than letting a click bypass the clamp.
     */
    public function canGoToToday(): bool
    {
        if ($this->isToday()) {
            return false;
        }

        if ($this->min !== null && $this->today->lt($this->min)) {
            return false;
        }

        if ($this->max !== null && $this->today->gt($this->max)) {
            return false;
        }

        return true;
    }

    /**
     * Compact visible label shown on the picker-trigger button.
     */
    public function shortLabel(): string
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->format('D j M Y'),
            Granularity::Week => $this->weekLabel($verbose = false),
            Granularity::Month => $this->current->format('F Y'),
        };
    }

    /**
     * Verbose label for screen-reader announcement via aria-label / live region.
     * Spells out days and months fully to avoid screen-reader abbreviation issues.
     */
    public function verboseLabel(): string
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->format('l j F Y'),
            Granularity::Week => $this->weekLabel($verbose = true),
            Granularity::Month => $this->current->format('F Y'),
        };
    }

    /**
     * Accessible label for the prev button, e.g. "Previous week".
     */
    public function previousAriaLabel(): string
    {
        return 'Previous ' . $this->granularity->value;
    }

    /**
     * Accessible label for the next button, e.g. "Next week".
     */
    public function nextAriaLabel(): string
    {
        return 'Next ' . $this->granularity->value;
    }

    /**
     * Accessible label for the today button — tells SR users whether it's actionable.
     */
    public function todayAriaLabel(): string
    {
        if ($this->isToday()) {
            return 'Today, current ' . $this->granularity->value . ' shown';
        }

        if (! $this->canGoToToday()) {
            return 'Go to today, not available in the current range';
        }

        return 'Go to today';
    }

    /**
     * Accessible label for the picker-trigger button — verbose, includes current period.
     */
    public function pickerAriaLabel(): string
    {
        return 'Select ' . $this->granularity->value . '. Currently ' . $this->verboseLabel() . '.';
    }

    /**
     * ISO week string for the current date's week (e.g. "2026-W17").
     * Handy for consumers that want to store state in the URL as an ISO week.
     * Returns null when granularity isn't week.
     */
    public function isoWeek(): ?string
    {
        return $this->granularity === Granularity::Week
            ? $this->current->format('o-\WW')
            : null;
    }

    /**
     * The date the consumer should treat as the period's anchor.
     * For week: the containing Monday. For month: the 1st. For day: the day itself.
     * Useful for consumers that need a normalised Y-m-d for their own queries.
     */
    public function periodStart(): string
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->toDateString(),
            Granularity::Week => $this->current->startOfWeek(CarbonImmutable::MONDAY)->toDateString(),
            Granularity::Month => $this->current->startOfMonth()->toDateString(),
        };
    }

    /**
     * End of the period (inclusive).
     */
    public function periodEnd(): string
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->toDateString(),
            Granularity::Week => $this->current->endOfWeek(CarbonImmutable::SUNDAY)->toDateString(),
            Granularity::Month => $this->current->endOfMonth()->toDateString(),
        };
    }

    private function step(int $direction): CarbonImmutable
    {
        return match ($this->granularity) {
            Granularity::Day => $this->current->addDays($direction),
            Granularity::Week => $this->current->addDays($direction * 7),
            Granularity::Month => $this->current->addMonthsNoOverflow($direction),
        };
    }

    private function weekLabel(bool $verbose): string
    {
        $start = $this->current->startOfWeek(CarbonImmutable::MONDAY);
        $end = $start->addDays(6);

        if ($verbose) {
            return sprintf(
                'Week of Monday %s to Sunday %s',
                $start->format('j F Y'),
                $end->format('j F Y'),
            );
        }

        // Compact form: collapse shared month/year where possible.
        // Same month: "20 – 26 Apr 2026"
        // Different months, same year: "28 Apr – 4 May 2026"
        // Different years: "29 Dec 2025 – 4 Jan 2026"
        if ($start->year !== $end->year) {
            return $start->format('j M Y') . ' – ' . $end->format('j M Y');
        }

        if ($start->month !== $end->month) {
            return $start->format('j M') . ' – ' . $end->format('j M Y');
        }

        return $start->format('j') . ' – ' . $end->format('j M Y');
    }

    private function parse(string $value, string $field): CarbonImmutable
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            throw new InvalidArgumentException(
                "{$field} must be a Y-m-d date string, got: {$value}"
            );
        }

        try {
            $parsed = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (\Throwable $e) {
            throw new InvalidArgumentException("{$field} is not a valid date: {$value}", 0, $e);
        }

        // Carbon silently overflows invalid dates (e.g. 2026-02-30 → 2026-03-02).
        // Round-trip compare to reject calendar-invalid inputs.
        if ($parsed->format('Y-m-d') !== $value) {
            throw new InvalidArgumentException("{$field} is not a valid date: {$value}");
        }

        return $parsed;
    }
}
