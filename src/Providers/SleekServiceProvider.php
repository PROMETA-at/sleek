<?php namespace Prometa\Sleek\Providers;

use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as BaseLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\{
  Component, 
  AnonymousComponent, 
  ComponentAttributeBag, 
  DynamicComponent,
};
use Prometa\Sleek\Blade\BladeCompiler;
use Prometa\Sleek\HandleQueryParametersMixin;
use Prometa\Sleek\Pagination\LengthAwarePaginator;
use Prometa\Sleek\Views\{
  Factory,
  SleekPageState,
  AnonymousComponent as ModifiedAnonymousComponent
};
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

        $this->app->bind(BaseLengthAwarePaginator::class , LengthAwarePaginator::class);
        Component::resolveComponentsUsing(function ($comp, $data) {
            if ($comp !== AnonymousComponent::class) return app()->make($comp, $data);
            else return new ModifiedAnonymousComponent($data['view'], $data['data']);
        });

        // Set the alias for the icon component to use x-icon instead of x-sleek::icon
        Blade::component('sleek::components.icon', 'icon');
        Blade::component('sleek::components.wrap-with', 'wrap-with');
        Blade::component('sleek::components.apply', 'apply');

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

            $bladeCompiler->directive('ensureSlotFor', function ($arguments) use ($bladeCompiler) {
                $parameters = explode(',', $arguments);
                [$var] = $parameters;

                $forceCreation = count($parameters) > 1
                    ? "if (!isset($var)) $var = new Illuminate\View\ComponentSlot();"
                    : '';

                // This mean hack creates a props statement for the given variable.
                //  Since the code is protected, and we don't want to duplicate it, we just call it via reflection!
                $varName = substr($var, 1);
                $propsCode = tap(new \ReflectionMethod(BladeCompiler::class, 'compileProps'))
                    ->setAccessible(true)
                    ->invoke($bladeCompiler, "(['$varName' => null])");

                return $propsCode . "\n<?php
                    $forceCreation
                    if (isset($var) && is_string($var)) $var = new Illuminate\View\ComponentSlot($var);
                ?>";
            });

            $bladeCompiler->directive('flags', function ($arguments) use ($bladeCompiler) {
                $array = eval('return '.$arguments.';');
                $flagPropsString = '[' . collect($array)
                    ->flatMap(function ($value, $key) {
                        $name = is_numeric($key) ? $value : $key;
                        $value = is_numeric($key) ? true : $value;

                        return [$name => $value, 'no' . ucfirst($name) => null];
                    })
                    ->map(fn ($value, $key) => "'$key' => " . ($value !== null ? json_encode($value) : 'null'))
                    ->join(', ') . ']';

                $propsCode = tap(new \ReflectionMethod(BladeCompiler::class, 'compileProps'))
                    ->setAccessible(true)
                    ->invoke($bladeCompiler, "($flagPropsString)");

                return $propsCode;
            });
            $bladeCompiler->directive('flag', function ($arguments) {
                $flagName = trim($arguments, '\'"');
                $negFlagName = 'no'.ucfirst($flagName);
                return "<?php if (
                    !(isset(\${$negFlagName}) && (\${$negFlagName} === true || \${$negFlagName} === 'true')) &&
                    (isset(\${$flagName}) && (\${$flagName} === true || \${$flagName} === 'true'))
                ): ?>";
            });
            $bladeCompiler->directive('unlessFlag', function ($arguments) {
                $flagName = trim($arguments, '\'"');
                $negFlagName = 'no'.ucfirst($flagName);
                return "<?php if (!(
                    !(isset(\${$negFlagName}) && (\${$negFlagName} === true || \${$negFlagName} === 'true')) &&
                    (isset(\${$flagName}) && (\${$flagName} === true || \${$flagName} === 'true'))
                )): ?>";
            });
            $bladeCompiler->directive('endflag', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('capture', function () {
                return '<?php ob_start(); ?>';
            });
            $bladeCompiler->directive('into', function ($variable) {
                return "<?php $variable = new \Illuminate\View\ComponentSlot(ob_get_clean()); ?>";
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

        Request::macro('urlWithQuery', function ($query) {
            /** @var $this ComponentAttributeBag */
            $question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

            return count($this->query()) > 0
                ? $question.Arr::query(array_merge($this->query(), $query))
                : $question.Arr::query($query);
        });

        Relation::macro('asSubquery', function () {
            /** @var Relation $this */

            if ($this instanceof HasManyThrough) $parentBuilder = $this->farParent->newQuery();
            else $parentBuilder = $this->getParent()->newQuery();

            return $this->getRelationExistenceQuery($this->getModel()->newQuery(), $parentBuilder, []);
        });

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'sleek');
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/sleek'),
        ]);

        Collection::macro('withEmptyOption', function ($label = null) {
            /** @var Collection $this */
            return $this->prepend($label ?? __('sleek::options.no-value'), '');
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
