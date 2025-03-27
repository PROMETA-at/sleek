<?php namespace Prometa\Sleek\Modules;

trait ComposesServiceProviders
{
    public function register(): void
    {
        $this->registerServiceProviders();
    }

    private function registerServiceProviders(): void
    {
        if (property_exists($this, 'serviceProviders')) {
            foreach ($this->serviceProviders as $serviceProvider) {
                $this->app->register($serviceProvider);
            }
        }
    }
}
