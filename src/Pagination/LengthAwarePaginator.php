<?php namespace Prometa\Sleek\Pagination;

use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;

class LengthAwarePaginator extends Paginator {
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
}
