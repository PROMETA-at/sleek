<?php

namespace Prometa\Sleek\Views\Builder;

use Prometa\Sleek\Views\SleekPageState;

class SleekLanguageBuilder
{
    private array $languages = [];

    public function __construct(private readonly SleekPageState $state)
    {
    }

    /**
     * Add a language to the list of supported languages.
     *
     * @param string $code
     * @param string $name
     * @return $this
     */
    public function add(string $code, string $name): self
    {
        $this->languages[$code] = $name;
        return $this;
    }

    public function de(string $name = 'Deutsch'): self
    {
        return $this->add('de', $name);
    }

    public function en(string $name = 'English'): self
    {
        return $this->add('en', $name);
    }

    public function fr(string $name = 'Français'): self
    {
        return $this->add('fr', $name);
    }

    public function es(string $name = 'Español'): self
    {
        return $this->add('es', $name);
    }

    public function it(string $name = 'Italiano'): self
    {
        return $this->add('it', $name);
    }

    public function build(): array
    {
        $this->state->language($this->languages);
        return $this->languages;
    }

    public function toArray(): array
    {
        return $this->languages;
    }
}
