<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Vite;
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
            'vite' => [
                'resources/sass/app.scss',
            ],
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.css',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.js',
            'https://unpkg.com/htmx.org@2.0.4/dist/htmx.js',
            'https://unpkg.com/hyperscript.org@0.9.14/dist/_hyperscript.js',
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Vite::useHotFile(__DIR__.'/../../public/hot');
//        Vite::useBuildDirectory(__DIR__.'/../../public');
    }
}
