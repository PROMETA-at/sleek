<?php namespace Prometa\Sleek;

use Illuminate\Contracts\Pagination\Paginator;

class HandleQueryParametersMixin
{
  public function autoSort(): \Closure
  {
    return function () {
      $sortBy = request('sort-by');
      $sortDirection = request('sort-direction', 'desc');

      if ($sortBy) {
        $this->orderBy($sortBy, $sortDirection);
      }

      return $this;
    };
  }

  public function autoPaginate(): \Closure {
    return function (int $defaultPageSize): Paginator {
      $pageSize = request('page-size', $defaultPageSize);

      return $this->paginate($pageSize);
    };
  }
}
