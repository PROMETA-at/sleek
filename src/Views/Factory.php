<?php namespace Prometa\Sleek\Views;

use Illuminate\View\Factory as BaseFactory;

class Factory extends BaseFactory
{
    public function slot($name, $content = null, $attributes = []): void
    {
        if (is_callable($content)) {
            $this->slots[$this->currentComponent()][$name] = new CallableComponentSlot(
                $content, $attributes
            );
            return;
        }

        parent::slot($name, $content, $attributes);
    }

    public function registerFragment($name, $content) {
        $this->fragments[$name] = $content;
    }

    protected ?string $selectedFragment = null;

    public function getSelectedFragment(): ?string
    {
        return $this->selectedFragment;
    }

    public function selectFragment(string $fragment): void
    {
        $this->selectedFragment = $fragment;
    }

    protected function viewInstance($view, $path, $data)
    {
        return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
    }
}
