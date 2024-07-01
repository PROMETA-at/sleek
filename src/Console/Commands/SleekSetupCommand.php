<?php

namespace Prometa\Sleek\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class SleekSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sleek:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the Sleek Package. Check & Install required npm Packages. Check & Fix SCSS Path to Sleek-SCSS';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bootstrapInstalled = $this->checkPackage('bootstrap');
        $bootstrapIconsInstalled = $this->checkPackage('bootstrap-icons');

        if ($bootstrapInstalled) {
            $this->info('Bootstrap is installed');
        } else {
            $this->warn('Bootstrap is not installed');
            if ($this->confirm('Do you want to install Bootstrap?')) {
                $this->installPackage('bootstrap');
            }
        }

        if ($bootstrapIconsInstalled) {
            $this->info('Bootstrap Icons are installed');
        } else {
            $this->warn('Bootstrap Icons are not installed');
            if ($this->confirm('Do you want to install Bootstrap Icons?')) {
                $this->installPackage('bootstrap-icons');
            }
        }

        $this->parseScssImport();
    }

    /**
     * Install the specified npm package.
     *
     * @param string $packageName
     * @return void
     */
    protected function installPackage(string $packageName) : void
    {
        $this->info("Installing {$packageName}...");
        $process = new Process(['npm', 'install', $packageName]);
        $process->run(function ($type, $buffer) {
            if(Process::ERR === $type) {
                $this->error('ERR > during Process');
                $this->output->write($buffer);
            }
            else
                $this->output->write($buffer);
        });

        if ($process->isSuccessful()) {
            $this->info("{$packageName} installed successfully");
        } else {
            $this->error("Failed to install {$packageName}!");
        }
    }

    protected function checkPackage($packageName) : bool
    {
        $packageJsonPath = base_path('package.json');

        if (!File::exists($packageJsonPath)) {
            $this->error('package.json file not found');
            return false;
        }

        $packageJsonContent = File::get($packageJsonPath);
        $packageJson = json_decode($packageJsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Error parsing package.json file');
            return false;
        }

        $dependencies = array_merge($packageJson['dependencies'] ?? [], $packageJson['devDependencies'] ?? []);

        return isset($dependencies[$packageName]);
    }

    /**
     *
     *
     * @return void
     */
    protected function parseScssImport() : void
    {
        $appScssPath = resource_path('sass/app.scss');
        $sleekScssImport = "@import '../../vendor/prometa/sleek/src/resources/sass/app.scss';";

        if (!File::exists($appScssPath)) {
            $this->warn('app.scss file not found');
            if ($this->confirm('The app.scss file does not exist. Do you want to create it and add the SCSS import?')) {
                File::put($appScssPath, "// This import was added by sleek:setup. It imports the entire CSS of bootstrap & bootstrap-icons.\n" . $sleekScssImport);
                $this->info('app.scss file has been created and SCSS path has been added');
            }
        } else {
            $appScssContent = File::get($appScssPath);

            if (!str_contains($appScssContent, $sleekScssImport)) {
                $this->warn('The SCSS path is not included in app.scss');
                if ($this->confirm('Do you want to add the correct SCSS path?')) {
                    File::append($appScssPath, "\n// This import was added by sleek:setup. It imports the entire CSS of bootstrap & bootstrap-icons.\n" . $sleekScssImport);
                    $this->info('SCSS path has been added');
                }
            } else {
                $this->info('The correct SCSS path is already included in app.scss');
            }
        }
    }
}
