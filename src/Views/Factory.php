<?php namespace Prometa\Sleek\Views;

use Illuminate\View\Factory as BaseFactory;

class Factory extends BaseFactory
{
    public function slot($name, $content = null, $attributes = [], ?array $scope = null): void
    {
        if (is_callable($content)) {
            // A registry-scoped slot ships its definition-site variables as $scope; bind them
            // into the callable so invocation sites stay argument-position-only. When $scope is
            // null (e.g. @forwardSlots passing an already-bound slot through) we must not re-wrap.
            if ($scope !== null) {
                $content = fn (...$args) => $content($scope, ...$args);
            }

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
