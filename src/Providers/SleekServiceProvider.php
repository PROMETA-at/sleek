<?php namespace Prometa\Sleek\Providers;

use Prometa\Sleek\Blade\BladeCompiler;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\DynamicComponent;

class SleekServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register() {
        $this->app->extend(\Illuminate\View\Compilers\BladeCompiler::class, function ($_, $app) {
            return new BladeCompiler($app['files'], $app['config']['view.compiled']);
        });

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->componentNamespace('Prometa\\Sleek\\Views\\Components', 'sleek');
        });
    }

    public function boot(): void
    {
        view()->composer('*', function ($view) {
            view()->share('view_name', $view->getName());
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'sleek');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/sleek'),
        ]);
    }
}
