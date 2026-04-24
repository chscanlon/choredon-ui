<?php

declare(strict_types=1);

/*
 | Pest configuration for the Choredon UI package.
 |
 | Unit tests exercise the pure-PHP support classes (date math, etc.)
 | without booting a Laravel application — they only depend on Carbon,
 | which is pulled in transitively by illuminate/support.
 */

pest()->extends(PHPUnit\Framework\TestCase::class)->in('Unit');
