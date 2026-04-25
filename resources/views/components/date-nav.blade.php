@php
    /**
     * @var \Choredon\Ui\Components\Support\DateNavState $state
     * @var string $currentDate
     * @var bool $showToday
     * @var string|null $ariaLabel
     */

    // Extract the bound property name from wire:model so prev/next/today
    // can $set it on the parent Livewire component.
    $wireModel = $attributes->wire('model');
    $property = $wireModel->value ?? null;

    $groupLabel = $ariaLabel ?? 'Date navigation';

    // Pre-compute wire:click handlers — Blade can't embed @if inside an
    // element's attribute list, so we resolve the strings here and emit
    // them unconditionally below.
    $prevClick = $property ? "\$set('{$property}', '{$state->previousDate()}')" : null;
    $nextClick = $property ? "\$set('{$property}', '{$state->nextDate()}')" : null;
    $todayClick = $property ? "\$set('{$property}', '{$state->today->toDateString()}')" : null;
@endphp

<div
    {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.lazy', 'wire:model.defer', 'wire:model.blur'])
        ->class([
            'choredon-date-nav',
            'flex flex-wrap items-center gap-2',
        ]) }}
    role="group"
    aria-label="{{ $groupLabel }}"
    data-choredon-date-nav
>
    {{-- Previous --}}
    <flux:button
        type="button"
        variant="ghost"
        square
        icon="chevron-left"
        :disabled="! $state->canGoPrevious()"
        :aria-label="$state->previousAriaLabel()"
        :wire:click="$prevClick"
    />

    {{-- Label / picker trigger --}}
    {{--
        min-w-[18ch] sizes the label slot for the widest possible label across
        all granularities (week cross-month/year is the longest, ~17-18ch),
        keeping the chevrons and Today button at stable horizontal positions
        as the user pages through dates. justify-center keeps the label
        visually balanced within the fixed slot.
    --}}
    <flux:dropdown position="bottom" align="center" gap="4">
        <flux:button
            type="button"
            variant="subtle"
            :aria-label="$state->pickerAriaLabel()"
            aria-haspopup="dialog"
            class="choredon-date-nav__label min-w-[18ch] justify-center"
        >
            {{ $state->shortLabel() }}
        </flux:button>

        <flux:popover class="choredon-date-nav__picker">
            {{--
                locale="en-GB" forces Monday-start week (Intl.Locale(...).getWeekInfo().firstDay)
                regardless of browser locale. Consistent with the component's ISO-week
                semantics (all internal period math uses CarbonImmutable::MONDAY).
                min/max propagate out-of-bounds greying to the calendar grid so the
                picker respects the same clamp as prev/next buttons.
            --}}
            @if($property)
                <flux:calendar
                    wire:model.live="{{ $property }}"
                    :value="$currentDate"
                    locale="en-GB"
                    :min="$state->min?->toDateString()"
                    :max="$state->max?->toDateString()"
                    x-on:input="$el.closest('[popover]')?.hidePopover()"
                />
            @else
                <flux:calendar
                    :value="$currentDate"
                    locale="en-GB"
                    :min="$state->min?->toDateString()"
                    :max="$state->max?->toDateString()"
                />
            @endif
        </flux:popover>
    </flux:dropdown>

    {{-- Today --}}
    {{--
        Below the sm breakpoint (640px) the "Today" word is hidden so the four
        controls fit cleanly on narrow viewports (~390px). The calendar-days
        icon remains as the affordance; aria-label keeps the button announceable
        to screen readers regardless of viewport.
    --}}
    @if($showToday)
        <flux:button
            type="button"
            variant="subtle"
            icon="calendar-days"
            :disabled="! $state->canGoToToday()"
            :aria-label="$state->todayAriaLabel()"
            :wire:click="$todayClick"
        >
            <span class="hidden sm:inline">{{ __('Today') }}</span>
        </flux:button>
    @endif

    {{-- Next --}}
    <flux:button
        type="button"
        variant="ghost"
        square
        icon="chevron-right"
        :disabled="! $state->canGoNext()"
        :aria-label="$state->nextAriaLabel()"
        :wire:click="$nextClick"
    />

    {{-- Screen-reader live region: announces full period whenever it changes --}}
    <span class="sr-only" aria-live="polite" aria-atomic="true">
        {{ $state->verboseLabel() }}
    </span>
</div>
