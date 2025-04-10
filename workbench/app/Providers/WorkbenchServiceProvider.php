<?php

namespace Workbench\App\Providers;

use Illuminate\Support\ServiceProvider;
use Prometa\Sleek\Facades\Sleek;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Sleek::authentication(false);
        Sleek::assets([
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.js',
            'https://unpkg.com/htmx.org@2.0.4/dist/htmx.js',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
