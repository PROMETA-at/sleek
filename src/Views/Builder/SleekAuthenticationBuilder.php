<?php

namespace Prometa\Sleek\Views\Builder;

use Prometa\Sleek\Views\SleekPageState;

class SleekAuthenticationBuilder
{
    private array $config = [];

    public function __construct(private readonly SleekPageState $state)
    {
    }

    public function disable(): self
    {
        $this->state->authentication(false);
        return $this;
    }

    public function loginRoute(string $route): self
    {
        $this->config['login'] = route($route);
        return $this;
    }

    public function loginUrl(string $url): self
    {
        $this->config['login'] = $url;
        return $this;
    }

    public function logoutRoute(string $route): self
    {
        $this->config['logout'] = route($route);
        return $this;
    }

    public function logoutUrl(string $url): self
    {
        $this->config['logout'] = $url;
        return $this;
    }

    public function build(): array
    {
        if (!empty($this->config)) {
            $this->state->authentication($this->config);
        }
        return $this->config;
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
