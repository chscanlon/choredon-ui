<?php

declare(strict_types=1);

use Choredon\Ui\Components\Support\DateNavState;
use Choredon\Ui\Components\Support\Granularity;

/*
 |--------------------------------------------------------------------------
 | Construction / validation
 |--------------------------------------------------------------------------
 */

it('constructs with a valid Y-m-d currentDate', function () {
    $state = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );

    expect($state->current->toDateString())->toBe('2026-04-20')
        ->and($state->today->toDateString())->toBe('2026-04-24')
        ->and($state->granularity)->toBe(Granularity::Week);
});

it('rejects non-Y-m-d currentDate', function () {
    new DateNavState(currentDate: '20/04/2026', granularity: Granularity::Week);
})->throws(InvalidArgumentException::class, 'currentDate must be a Y-m-d');

it('rejects out-of-bounds dates like Feb 30', function () {
    new DateNavState(currentDate: '2026-02-30', granularity: Granularity::Day);
})->throws(InvalidArgumentException::class);

it('rejects min greater than max', function () {
    new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        min: '2026-05-01',
        max: '2026-04-01',
    );
})->throws(InvalidArgumentException::class, 'min must be on or before max');

it('defaults today to the server-time today when none provided', function () {
    $state = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Day);

    expect($state->today->toDateString())->toBe(date('Y-m-d'));
});

/*
 |--------------------------------------------------------------------------
 | previousDate / nextDate step arithmetic
 |--------------------------------------------------------------------------
 */

it('steps by 1 day for Day granularity', function () {
    $state = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Day);

    expect($state->previousDate())->toBe('2026-04-19')
        ->and($state->nextDate())->toBe('2026-04-21');
});

it('steps by 7 days for Week granularity, preserving day-of-week', function () {
    // Friday 2026-04-24
    $state = new DateNavState(currentDate: '2026-04-24', granularity: Granularity::Week);

    expect($state->previousDate())->toBe('2026-04-17') // also a Friday
        ->and($state->nextDate())->toBe('2026-05-01'); // also a Friday
});

it('steps by 1 month for Month granularity, no overflow', function () {
    // 31 March — previous month (Feb) has 28 days in 2026
    $state = new DateNavState(currentDate: '2026-03-31', granularity: Granularity::Month);

    expect($state->previousDate())->toBe('2026-02-28')
        ->and($state->nextDate())->toBe('2026-04-30');
});

/*
 |--------------------------------------------------------------------------
 | canGoPrevious / canGoNext min-max clamping
 |--------------------------------------------------------------------------
 */

it('allows navigation when no min/max set', function () {
    $state = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Week);

    expect($state->canGoPrevious())->toBeTrue()
        ->and($state->canGoNext())->toBeTrue();
});

it('disables previous when step would go before min', function () {
    $state = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        min: '2026-04-20',
    );

    expect($state->canGoPrevious())->toBeFalse()
        ->and($state->canGoNext())->toBeTrue();
});

it('disables next when step would go after max', function () {
    $state = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        max: '2026-04-20',
    );

    expect($state->canGoPrevious())->toBeTrue()
        ->and($state->canGoNext())->toBeFalse();
});

/*
 |--------------------------------------------------------------------------
 | isToday semantics per granularity
 |--------------------------------------------------------------------------
 */

it('detects today by exact day for Day granularity', function () {
    $state = new DateNavState(
        currentDate: '2026-04-24',
        granularity: Granularity::Day,
        today: '2026-04-24',
    );
    expect($state->isToday())->toBeTrue();

    $other = new DateNavState(
        currentDate: '2026-04-23',
        granularity: Granularity::Day,
        today: '2026-04-24',
    );
    expect($other->isToday())->toBeFalse();
});

it('detects today by ISO-week containment for Week granularity', function () {
    // 2026-04-20 (Mon) and 2026-04-24 (Fri) share the same ISO week.
    $state = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );
    expect($state->isToday())->toBeTrue();

    // 2026-04-27 (Mon next week) is NOT today's week.
    $other = new DateNavState(
        currentDate: '2026-04-27',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );
    expect($other->isToday())->toBeFalse();
});

it('detects today by year-month for Month granularity', function () {
    $state = new DateNavState(
        currentDate: '2026-04-01',
        granularity: Granularity::Month,
        today: '2026-04-30',
    );
    expect($state->isToday())->toBeTrue();

    $other = new DateNavState(
        currentDate: '2026-05-15',
        granularity: Granularity::Month,
        today: '2026-04-30',
    );
    expect($other->isToday())->toBeFalse();
});

/*
 |--------------------------------------------------------------------------
 | shortLabel / verboseLabel formatting
 |--------------------------------------------------------------------------
 */

it('formats Day labels', function () {
    $state = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Day);

    expect($state->shortLabel())->toBe('Mon 20 Apr 2026')
        ->and($state->verboseLabel())->toBe('Monday 20 April 2026');
});

it('formats Week labels (same month)', function () {
    $state = new DateNavState(currentDate: '2026-04-22', granularity: Granularity::Week);

    // Week = Mon 20 Apr to Sun 26 Apr 2026
    expect($state->shortLabel())->toBe('20 – 26 Apr 2026')
        ->and($state->verboseLabel())->toBe('Week of Monday 20 April 2026 to Sunday 26 April 2026');
});

it('formats Week labels (cross-month)', function () {
    // Wed 2026-04-29 → week Mon 27 Apr – Sun 3 May
    $state = new DateNavState(currentDate: '2026-04-29', granularity: Granularity::Week);

    expect($state->shortLabel())->toBe('27 Apr – 3 May 2026');
});

it('formats Week labels (cross-year)', function () {
    // Wed 2025-12-31 → week Mon 29 Dec 2025 – Sun 4 Jan 2026
    $state = new DateNavState(currentDate: '2025-12-31', granularity: Granularity::Week);

    expect($state->shortLabel())->toBe('29 Dec 2025 – 4 Jan 2026');
});

it('formats Month labels', function () {
    $state = new DateNavState(currentDate: '2026-04-15', granularity: Granularity::Month);

    expect($state->shortLabel())->toBe('April 2026')
        ->and($state->verboseLabel())->toBe('April 2026');
});

/*
 |--------------------------------------------------------------------------
 | periodStart / periodEnd anchoring
 |--------------------------------------------------------------------------
 */

it('anchors Week period to Monday–Sunday', function () {
    // Fri 2026-04-24 is in the week Mon 20 – Sun 26 April 2026
    $state = new DateNavState(currentDate: '2026-04-24', granularity: Granularity::Week);

    expect($state->periodStart())->toBe('2026-04-20')
        ->and($state->periodEnd())->toBe('2026-04-26');
});

it('anchors Month period to 1st – last day', function () {
    $state = new DateNavState(currentDate: '2026-04-15', granularity: Granularity::Month);

    expect($state->periodStart())->toBe('2026-04-01')
        ->and($state->periodEnd())->toBe('2026-04-30');
});

it('anchors Day period to itself', function () {
    $state = new DateNavState(currentDate: '2026-04-24', granularity: Granularity::Day);

    expect($state->periodStart())->toBe('2026-04-24')
        ->and($state->periodEnd())->toBe('2026-04-24');
});

/*
 |--------------------------------------------------------------------------
 | isoWeek helper
 |--------------------------------------------------------------------------
 */

it('returns ISO week string for Week granularity', function () {
    $state = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Week);
    expect($state->isoWeek())->toBe('2026-W17');
});

it('returns null isoWeek for Day / Month granularities', function () {
    $day = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Day);
    $month = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Month);

    expect($day->isoWeek())->toBeNull()
        ->and($month->isoWeek())->toBeNull();
});

it('handles ISO-week year boundary correctly', function () {
    // 2025-12-29 is Mon of ISO week 2026-W01 (week-year 2026)
    $state = new DateNavState(currentDate: '2025-12-29', granularity: Granularity::Week);

    expect($state->isoWeek())->toBe('2026-W01');
});

/*
 |--------------------------------------------------------------------------
 | Accessibility label helpers
 |--------------------------------------------------------------------------
 */

it('includes granularity in prev/next aria-labels', function () {
    $week = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Week);
    expect($week->previousAriaLabel())->toBe('Previous week')
        ->and($week->nextAriaLabel())->toBe('Next week');

    $day = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Day);
    expect($day->previousAriaLabel())->toBe('Previous day')
        ->and($day->nextAriaLabel())->toBe('Next day');

    $month = new DateNavState(currentDate: '2026-04-20', granularity: Granularity::Month);
    expect($month->previousAriaLabel())->toBe('Previous month')
        ->and($month->nextAriaLabel())->toBe('Next month');
});

it('reflects isToday state in todayAriaLabel', function () {
    $current = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );
    expect($current->todayAriaLabel())->toBe('Today, current week shown');

    $past = new DateNavState(
        currentDate: '2026-04-06',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );
    expect($past->todayAriaLabel())->toBe('Go to today');
});

/*
 |--------------------------------------------------------------------------
 | canGoToToday: respects min/max bounds AND already-on-today state
 |--------------------------------------------------------------------------
 */

it('disables canGoToToday when already on today', function () {
    $state = new DateNavState(
        currentDate: '2026-04-20',
        granularity: Granularity::Week,
        today: '2026-04-24',
    );

    expect($state->canGoToToday())->toBeFalse()
        ->and($state->todayAriaLabel())->toBe('Today, current week shown');
});

it('disables canGoToToday when today is past max (completed-weeks pattern)', function () {
    // Consumer is "last completed week only". Today is in current incomplete week.
    $state = new DateNavState(
        currentDate: '2026-04-13',   // W16 Mon (last completed)
        granularity: Granularity::Week,
        today: '2026-04-24',         // in W17 (incomplete)
        max: '2026-04-13',           // clamp to last completed
    );

    expect($state->canGoToToday())->toBeFalse()
        ->and($state->todayAriaLabel())->toBe('Go to today, not available in the current range');
});

it('disables canGoToToday when today is before min', function () {
    $state = new DateNavState(
        currentDate: '2030-01-01',
        granularity: Granularity::Day,
        today: '2026-04-24',
        min: '2028-01-01',
    );

    expect($state->canGoToToday())->toBeFalse();
});

it('enables canGoToToday when today is in range and we are not already there', function () {
    $state = new DateNavState(
        currentDate: '2026-04-06',
        granularity: Granularity::Week,
        today: '2026-04-24',
        min: '2026-01-01',
        max: '2026-12-31',
    );

    expect($state->canGoToToday())->toBeTrue()
        ->and($state->todayAriaLabel())->toBe('Go to today');
});

it('includes verbose period label in pickerAriaLabel', function () {
    $state = new DateNavState(currentDate: '2026-04-22', granularity: Granularity::Week);

    expect($state->pickerAriaLabel())
        ->toBe('Select week. Currently Week of Monday 20 April 2026 to Sunday 26 April 2026.');
});
