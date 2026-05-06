<?php namespace Prometa\Sleek\Pagination;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;

class LengthAwarePaginator extends Paginator {
    /**
     * Number of pages to keep pinned at each edge of the pagination window.
     *
     * Combined with the inherited {@see $onEachSide} (the per-side slider
     * size around the current page), this controls the rendered window:
     *
     *   - Middle case: `[1..B] ... [current-N..current+N] ... [last-B+1..last]`
     *   - Border case (current ≤ N+B or current > last-N-B): the slider
     *     absorbs the adjacent border and expands so the rendered range from
     *     that edge spans `2*N + B` pages. This keeps the visible button
     *     count roughly constant as the user pages through.
     *
     * @var int
     */
    public $borderWindowSize = 2;

    /**
     * Set the number of pages to keep pinned at each edge.
     *
     * @return $this
     */
    public function borderWindowSize(int $count)
    {
        $this->borderWindowSize = $count;

        return $this;
    }

    /**
     * Get the URL for a given page number.
     *
     * @param  int  $page
     * @return string
     */
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = count($this->query) > 0 
            ? [...$this->query]
            : [];
        Arr::set($parameters, $this->pageName, $page);

        return $this->path()
                        .(str_contains($this->path(), '?') ? '&' : '?')
                        .Arr::query($parameters)
                        .$this->buildFragment();
    }

    /**
     * Get the array of elements to pass to the view.
     *
     * Overrides Laravel's default to use sleek's {@see UrlWindow}, which
     * keeps the window tight around the current page instead of bloating
     * near the edges.
     *
     * @return array
     */
    protected function elements()
    {
        return UrlWindow::make($this);
    }
}
