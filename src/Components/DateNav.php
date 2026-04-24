<?php

declare(strict_types=1);

namespace Choredon\Ui\Components;

use Choredon\Ui\Components\Support\DateNavState;
use Choredon\Ui\Components\Support\Granularity;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-choredon::date-nav />
 *
 * Prev / label / today / next — with a pop-over Flux calendar behind the label.
 * Designed to replace hand-rolled year/week selects or prev/next button groups
 * across Choredon-based apps.
 *
 * The consumer's Livewire component owns the bound property (usually via
 * wire:model.live="currentDate"). This component is a pure render-time
 * formatter + a set of prev/next/today click handlers that update that
 * property. No internal Livewire state.
 *
 * Timezone note: this component is timezone-agnostic. The consumer is
 * responsible for supplying :today in the app's business timezone,
 * e.g. now('Australia/Sydney')->toDateString(). If :today is omitted it
 * defaults to the server's configured timezone, which is usually but not
 * always correct.
 *
 * Accessibility: targets WCAG 2.1 AA. role="group" + aria-label on the root,
 * verbose aria-labels on all buttons, aria-live region announcing period
 * changes, 44px touch targets via the --size-touch-target token. Focus
 * management (return-to-trigger on picker close) is inherited from Flux.
 */
final class DateNav extends Component
{
    public DateNavState $state;

    public function __construct(
        public string $currentDate,
        string $granularity = 'week',
        ?string $today = null,
        ?string $min = null,
        ?string $max = null,
        public bool $showToday = true,
        public ?string $ariaLabel = null,
    ) {
        $this->state = new DateNavState(
            currentDate: $currentDate,
            granularity: Granularity::from($granularity),
            today: $today,
            min: $min,
            max: $max,
        );
    }

    public function render(): View
    {
        return view('choredon::components.date-nav');
    }
}
