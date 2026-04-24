<?php

declare(strict_types=1);

namespace Choredon\Ui\Components\Support;

/**
 * Granularity of date navigation: how large a step prev/next takes
 * and how the visible label is computed.
 */
enum Granularity: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
}
