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

  public function autoFilter(): \Closure {
    return function (?array $filters = null): self {
      $filters = $filters ?? $this->getModel()->filterConfiguration;

      foreach ($filters as $fieldName => $pipeline) {
        if (request()->has($fieldName)) {
          FilterPipeline::from($pipeline)->apply($this, $fieldName, request($fieldName));
        }
      }

      return $this;
    };
  }
}
