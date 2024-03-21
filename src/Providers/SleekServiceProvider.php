<?php namespace Prometa\Sleek\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\DynamicComponent;
use Prometa\Sleek\Blade\BladeCompiler;
use Prometa\Sleek\HandleQueryParametersMixin;
use prometa\Sleek\Middleware\SetLocale;
use Prometa\Sleek\Views\Factory;
use Prometa\Sleek\Views\SleekPageState;
use Illuminate\Support\Facades\Route;

class SleekServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->extend(\Illuminate\View\Compilers\BladeCompiler::class, function ($_, $app) {
            return new BladeCompiler($app['files'], $app['config']['view.compiled']);
        });

        $this->app->extend('view', function ($_, $app) {
            $factory = new Factory($app['view.engine.resolver'], $app['view.finder'], $app['events']);
            $factory->setContainer($app);
            $factory->share('app', $app);
            return $factory;
        });

        $this->callAfterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->componentNamespace('Prometa\\Sleek\\Views\\Components', 'sleek');
        });

        $this->app->scoped('sleek', fn() => new SleekPageState);

        view()->composer('sleek::*', function () {
            /** @var SleekPageState $pageState */
            $pageState = app()->make('sleek');


            view()->share('sleek::navbar', $pageState->resolveMenuStructure());
            view()->share('sleek::logo', $pageState->resolveLogo());
            view()->share('sleek::theme', $pageState->resolveTheme());
            view()->share('sleek::particle', $pageState->resolveParticle());
            view()->share('sleek::authentication', $pageState->resolveAuthentication());
            view()->share('sleek::document', $pageState->resolveDocument());
            view()->share('sleek::language', $pageState->resolveLanguage());
            view()->share('sleek::alert', $pageState->resolveAlert());
            view()->share('sleek::assets', $pageState->resolveAssets());
        });


        \Illuminate\Database\Eloquent\Builder::mixin(new HandleQueryParametersMixin);
        \Illuminate\Database\Query\Builder::mixin(new HandleQueryParametersMixin);
        \Illuminate\Database\Eloquent\Relations\Relation::mixin(new HandleQueryParametersMixin);

        $this->booted(function () {
            Route::middleware('web')->group(__DIR__ . '/../routes/web.php');
        });
    }

    public function boot(\Illuminate\Contracts\Http\Kernel $kernel): void
    {
        Blade::component('dynamic-component', DynamicComponent::class);
        $kernel->appendMiddlewareToGroup('web', \Prometa\Sleek\Middleware\LocaleMiddleware::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sleek');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sleek'),
        ]);
        Paginator::defaultView('sleek::pagination');

    }
}
