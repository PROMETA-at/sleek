<?php namespace Prometa\Sleek\Views;

use Throwable;

class View extends \Illuminate\View\View
{
    /**
     * Get the string contents of the view.
     *
     * @param  callable|null  $callback
     * @return string
     *
     * @throws \Throwable
     */
    public function render(callable $callback = null)
    {
        try {
            $contents = $this->renderContents();

            $response = null;
            if (isset($callback)) $response = $callback($this, $contents);
            if (
                $this->factory->doneRendering() &&
                $fragment = $this->factory->getSelectedFragment()
            ) $response = $this->factory->getFragment($fragment);

            // Once we have the contents of the view, we will flush the sections if we are
            // done rendering all views so that there is nothing left hanging over when
            // another view gets rendered in the future by the application developer.
            $this->factory->flushStateIfDoneRendering();

            return ! is_null($response) ? $response : $contents;
        } catch (Throwable $e) {
            $this->factory->flushState();

            throw $e;
        }
    }
}