<?php

namespace Prometa\Sleek\Views\Builder;

use Prometa\Sleek\Views\SleekPageState;

class SleekAlertBuilder
{
    private array $config = [];

    public function __construct(private readonly SleekPageState $state)
    {
    }

    /**
     * Set a custom alert position.
     *
     * @param string $position The position identifier ('top-right', 'bottom-right', 'center', 'bottom')
     * @return self
     */
    public function position(string $position): self
    {
        $this->config['position'] = $position;
        return $this;
    }

    /**
     * Position alerts at the top-right corner (default).
     *
     * @return self
     */
    public function topRight(): self
    {
        return $this->position('top-right');
    }

    /**
     * Position alerts at the bottom-right corner.
     *
     * @return self
     */
    public function bottomRight(): self
    {
        return $this->position('bottom-right');
    }

    /**
     * Position alerts at the center top of the screen.
     *
     * @return self
     */
    public function center(): self
    {
        return $this->position('center');
    }

    /**
     * Position alerts at the bottom center of the screen.
     *
     * @return self
     */
    public function bottom(): self
    {
        return $this->position('bottom');
    }

    public function build(): array
    {
        $this->state->alert($this->config);
        return $this->config;
    }

    public function toArray(): array
    {
        return $this->config;
    }
}
