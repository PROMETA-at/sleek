<?php namespace Prometa\Sleek\Modules;

use Illuminate\Console\Application as Artisan;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait ScansForCommands
{
    public function boot(): void
    {
        if (app()->runningInConsole()) {
            $this->scanForCommands();
        }
    }

    protected function scanForCommands(): void
    {
        $paths = array_unique(Arr::wrap(app_path($this->moduleName())));
        $paths = array_filter($paths, fn($path) => is_dir($path));
        if (empty($paths)) return;

        $namespace = $this->app->getNamespace();

        foreach (Finder::create()->in($paths)->files() as $file) {
            $command = $this->commandClassFromFile($file, $namespace);

            if (is_subclass_of($command, Command::class) &&
                ! (new ReflectionClass($command))->isAbstract()) {
                Artisan::starting(function ($artisan) use ($command) {
                    $artisan->resolve($command);
                });
            }
        }
    }

    protected function commandClassFromFile(SplFileInfo $file, string $namespace): string
    {
        return $namespace.str_replace(
                ['/', '.php'],
                ['\\', ''],
                Str::after($file->getRealPath(), realpath(app_path()).DIRECTORY_SEPARATOR)
            );
    }

    protected abstract function moduleName(): string;
}
