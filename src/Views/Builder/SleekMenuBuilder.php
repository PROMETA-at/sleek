<?php

namespace Prometa\Sleek\Views\Builder;

use Illuminate\Support\Collection;
use Prometa\Sleek\Views\SleekPageState;

class SleekMenuBuilder
{
    private Collection $items;
    private array $config = [];

    public function __construct(private readonly SleekPageState $state)
    {
        $this->items = collect();
    }

    /**
     * Add a menu item with label, route, and optional icon.
     *
     * @param string $label The display text for the menu item
     * @param string|null $route The URL or route path for the menu item
     * @param string|null $icon The icon identifier (e.g., 'home', 'users', 'cog')
     * @return self
     *
     */
    public function item(
        string $label,
        string $route = null,
        string $icon = null,
    ): self {
        $this->items->push(array_filter([
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
        ]));

        return $this;
    }

    /**
     * Add a dropdown menu with sub-items.
     *
     * @param string $label The dropdown label text
     * @param callable $callback Callback function to build sub-menu items
     * @param string|null $icon Optional icon identifier for the dropdown
     * @return self
     *
     * @example
     * ->dropdown('Admin', function($menu) {
     *     $menu->item('Users', '/users', 'users')
     *          ->item('Settings', '/settings', 'cog');
     * }, 'admin')
     */
    public function dropdown(string $label, callable $callback, string $icon = null): self
    {
        $subBuilder = new self($this->state);
        $callback($subBuilder);

        $this->items->push(array_filter([
            'label' => $label,
            'icon' => $icon,
            'items' => $subBuilder->items->toArray()
        ]));

        return $this;
    }

    /**
     * Add menu items conditionally based on a condition.
     *
     * @param bool $condition The condition to evaluate
     * @param callable $callback Callback executed if condition is true
     * @return self
     *
     * @example
     * ->when(auth()->check(), function($menu) {
     *     $menu->item('Profile', '/profile', 'user')
     *          ->item('Logout', '/logout', 'sign-out');
     * })
     */
    public function when(bool $condition, callable $callback): self
    {
        if ($condition) {
            $callback($this);
        }
        return $this;
    }

    /**
     * Set the navigation bar position.
     *
     * @param string $position The position ('side' for vertical sidebar, 'top' for horizontal top bar)
     * @return self
     */
    public function position(string $position): self
    {
        $this->config['position'] = $position;
        return $this;
    }

    /**
     * Set the view for the extra section in the navbar (the upper placeholder between menu items and navbar logo)
     * @param string $view
     * @return $this
     */
    public function extra(string $view): self
    {
        $this->config['extra'] = $view;
        return $this;
    }

    /**
     * Set the view for the account section in the navbar (the lower placeholder)
     * @param string $view
     * @return $this
     */
    public function account(string $view): self
    {
        $this->config['account'] = $view;
        return $this;
    }

    public function build(): array
    {
        $menuData = array_merge($this->config, $this->items->toArray());

        $this->state->menu($menuData);

        return $menuData;
    }

    public function toArray(): array
    {
        return array_merge($this->config, $this->items->toArray());
    }
}
