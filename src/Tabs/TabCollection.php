<?php

namespace Prometa\Sleek\Tabs;

use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;

/**
 * @implements Collection<array-key, Tab>
 */
class TabCollection extends Collection
{
    /**
     * @param array<Tab>|Enumerable<array-key, Tab> $items
     */
    public function __construct(
        $items = [],
    ) {
        parent::__construct($items);
    }

    public function current(): ?Tab {
        return $this->first(fn (Tab $tab) => $tab->active, $this->first());
    }
}