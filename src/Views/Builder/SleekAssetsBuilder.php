<?php

namespace Prometa\Sleek\Views\Builder;

use Illuminate\Support\Collection;
use Prometa\Sleek\Views\SleekPageState;

class SleekAssetsBuilder
{
    private array $config = [];
    private Collection $assets;

    public function __construct(private readonly SleekPageState $state)
    {
        $this->assets = collect();
    }

    /**
     * Configure Vite assets for hot reloading and building.
     *
     * @param array $files Array of asset file paths for Vite
     * @return self
     *
     * @example
     * ->vite(['resources/sass/app.scss', 'resources/js/app.js'])
     */
    public function vite(array $files): self
    {
        $this->config['vite'] = $files;
        return $this;
    }

    /**
     * Add a CSS file to be included in the document head.
     *
     * @param string $url The CSS file URL or path
     * @return self
     *
     * @example
     * ->css('https://cdn.example.com/library.css')
     */
    public function css(string $url): self
    {
        $this->assets->push($url);
        return $this;
    }

    /**
     * Add a JavaScript file to be included in the document.
     *
     * @param string $url The JavaScript file URL or path
     * @return self
     *
     * @example
     * ->js('https://cdn.example.com/library.js')
     */
    public function js(string $url): self
    {
        $this->assets->push($url);
        return $this;
    }

    public function favicon(string $href, string $type = 'image/x-icon', string $rel = 'icon'): self
    {
        if (!isset($this->config['favicon'])) {
            $this->config['favicon'] = [];
        }

        $this->config['favicon'][] = compact('href', 'type', 'rel');
        return $this;
    }

    public function build(): array
    {
        $assets = array_merge($this->config, $this->assets->toArray());

        $this->state->assets($assets);
        return $assets;
    }

    public function toArray(): array
    {
        return array_merge($this->config, $this->assets->toArray());
    }
}
