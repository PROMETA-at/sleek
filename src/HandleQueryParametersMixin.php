<?php namespace Prometa\Sleek;

use Illuminate\Contracts\Pagination\Paginator;
use function Prometa\Sleek\as_parameter_name;

class HandleQueryParametersMixin
{
  public function autoSort(): \Closure
  {
    return function ($prefix = null) {
      if ($prefix) $prefix .= '.';
      $sortBy = request($prefix.'sort-by');
      $sortDirection = request($prefix.'sort-direction', 'desc');

      if ($sortBy) {
        $this->orderBy($sortBy, $sortDirection);
      }

      return $this;
    };
  }

  public function autoPaginate(): \Closure {
    return function (int $defaultPageSize, $prefix = null): Paginator {
      if ($prefix) $prefix .= '.';
      $pageSize = request($prefix.'page-size', $defaultPageSize);

      return $this->paginate($pageSize, pageName: as_parameter_name($prefix.'page'));
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
