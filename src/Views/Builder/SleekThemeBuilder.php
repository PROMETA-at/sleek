<?php

namespace Prometa\Sleek\Views\Builder;

use Prometa\Sleek\Views\SleekPageState;

class SleekThemeBuilder
{
    private array $config = [];

    public function __construct(private readonly SleekPageState $state)
    {
    }

    public function logo(string $route = '/', string $image = null): self
    {
        $this->config['logo'] = array_filter(compact('route', 'image'));
        return $this;
    }

    private function color(string $name, string $value): self
    {
        if (!isset($this->config['colors'])) {
            $this->config['colors'] = [];
        }

        $this->config['colors'][$name] = $value;
        return $this;
    }

    public function primary(string $color): self
    {
        return $this->color('primary', $color);
    }

    public function secondary(string $color): self
    {
        return $this->color('secondary', $color);
    }

    public function success(string $color): self
    {
        return $this->color('success', $color);
    }

    public function danger(string $color): self
    {
        return $this->color('danger', $color);
    }

    public function warning(string $color): self
    {
        return $this->color('warning', $color);
    }

    public function info(string $color): self
    {
        return $this->color('info', $color);
    }

    public function build(): array
    {
        $this->state->theme($this->config);
        return $this->config;
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
