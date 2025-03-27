<?php namespace Prometa\Sleek\Modules;

use Closure;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\View;

trait BootstrapsModule
{
    use ScansForCommands {
        boot as bootScansForCommands;
    }
    use ComposesServiceProviders {
        register as registerComposesServiceProviders;
    }

    private string|null $moduleName = null;
    private Closure|null $loadRoutesUsing = null;

    public function register(): void
    {
        $this->registerComposesServiceProviders();

        if (method_exists($this, 'registerModuleGate'))
            call_user_func([$this, 'registerModuleGate']);

        $this->booted(function () {
            $this->loadRoutes();

            if ($this->app->runningInConsole() && method_exists($this, 'schedule'))
                $this->app->afterResolving(Schedule::class, function (Schedule $schedule) {
                    $this->schedule($schedule);
                });
        });
    }

    public function boot(): void
    {
        $module_name = $this->moduleName();
        View::addNamespace($module_name, app_path(implode('/', [$module_name])));
        $this->loadTranslationsFrom(app_path(implode(DIRECTORY_SEPARATOR, [$module_name, 'lang'])), $module_name);

        if (app()->runningInConsole()) {
            $this->registerMigrations();
        }

        $this->bootScansForCommands();
    }

    protected function routes(Closure $routesCallback): static
    {
        $this->loadRoutesUsing = $routesCallback;

        return $this;
    }

    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom(app_path(implode(DIRECTORY_SEPARATOR, [$this->moduleName(), 'migrations'])));
    }

    private function loadRoutes(): void
    {
        if (! is_null($this->loadRoutesUsing)) {
            $this->app->call($this->loadRoutesUsing);
        } elseif (method_exists($this, 'map')) {
            $this->app->call([$this, 'map']);
        }
    }

    private function moduleName(): string
    {
        return $this->moduleName ?? self::guessModuleName();
    }

    private static function guessModuleName() {
        $normalized_class_name = str_replace('\\', '/', static::class);
        $namespace_parts = collect(explode('/', $normalized_class_name));
        $namespace_parts->pop();

        return $namespace_parts->pop();
    }

    protected abstract function loadMigrationsFrom($paths);
    protected abstract function loadTranslationsFrom($path, $namespace);
}
