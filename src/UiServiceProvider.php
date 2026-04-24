<?php

declare(strict_types=1);

namespace Choredon\Ui;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers Choredon's Blade component namespace and views so consumers
 * can write <x-choredon::component-name /> in their templates.
 *
 * The adapter CSS files in dist/ are consumed directly by consumer apps
 * (imported into their CSS entry point) and are not wired here.
 */
class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'choredon');

        Blade::componentNamespace('Choredon\\Ui\\Components', 'choredon');
    }
}
