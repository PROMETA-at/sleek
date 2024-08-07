<?php namespace Prometa\Sleek\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ComponentAttributeBag;
use Illuminate\View\DynamicComponent;
use Prometa\Sleek\Blade\BladeCompiler;
use Prometa\Sleek\HandleQueryParametersMixin;
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
            $bladeCompiler->component('dynamic-component', DynamicComponent::class);
            $bladeCompiler->componentNamespace('Prometa\\Sleek\\Views\\Components', 'sleek');
            $bladeCompiler->anonymousComponentPath(__DIR__.'/../resources/views/bootstrap/components', 'bs');
            $bladeCompiler->directive('forwardSlots', function () {
              return '<?php foreach ($__laravel_slots as $slotName => $slotContent) {
                if ($slotName === "__default") continue;
                $__env->slot($slotName, $slotContent, $slotContent->attributes);
              } ?>';
            });
        });

        $this->app->scoped('sleek', fn() => new SleekPageState);

        view()->composer('sleek::*', function () {
            /** @var SleekPageState $pageState */
            $pageState = app()->make('sleek');


            view()->share('sleek::navItems', $pageState->resolveMenuStructure());
            view()->share('sleek::navPosition', $pageState->resolveMenuPosition());
            view()->share('sleek::logo', $pageState->resolveLogo());
            view()->share('sleek::theme', $pageState->resolveTheme());

            view()->share('sleek::nav:extra', $pageState->resolveNavbarExtra());
            view()->share('sleek::nav:account', $pageState->resolveNavbarAccount());

            view()->share('sleek::authentication', $pageState->resolveAuthentication());
            view()->share('sleek::document', $pageState->resolveDocument());
            view()->share('sleek::language', $pageState->resolveLanguage());
            view()->share('sleek::alert', $pageState->resolveAlert());
            view()->share('sleek::assets', $pageState->resolveAssets());
        });


        \Illuminate\Database\Eloquent\Builder::mixin(new HandleQueryParametersMixin);
        \Illuminate\Database\Query\Builder::mixin(new HandleQueryParametersMixin);
        \Illuminate\Database\Eloquent\Relations\Relation::mixin(new HandleQueryParametersMixin);

        ComponentAttributeBag::macro('trimPrefix', function ($prefix) {
          /** @var $this ComponentAttributeBag */
          return new static(
            collect($this->getAttributes())
              ->filter(fn ($_, $key) => str_starts_with($key, $prefix))
              ->mapWithKeys(fn ($v, $k) => [ substr($k, strlen($prefix)) => $v])
              ->toArray());
        });

        $this->booted(function () {
            Route::middleware('web')->group(__DIR__ . '/../routes/web.php');
        });

        $this->commands([
            \Prometa\Sleek\Console\Commands\SleekSetupCommand::class,
        ]);
    }

    public function boot(\Illuminate\Contracts\Http\Kernel $kernel): void
    {
        $kernel->appendMiddlewareToGroup('web', \Prometa\Sleek\Middleware\LocaleMiddleware::class);

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'sleek');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/sleek'),
        ]);
        Paginator::defaultView('sleek::pagination');

    }
}
