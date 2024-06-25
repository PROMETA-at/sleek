<?php namespace Prometa\Sleek;

use Illuminate\Database\Query\Builder;

class FilterPipeline
{
  public function __construct(array $pipeline) {
    $this->pipeline = array_map(function ($filter) {
      if (is_string($filter) && method_exists($this, $filter)) {
        $filter = [$this, $filter];
      }

      if (!is_callable($filter)) {
        throw new \InvalidArgumentException('Invalid filter');
      }

      return $filter;
    }, $pipeline);
  }

  public static function from(array|string $pipeline): static {
    if (is_string($pipeline)) {
      $pipeline = explode('|', $pipeline);
    }

    return new static($pipeline);
  }

  public function apply($query, $field, $value) {
    $applyPipeline = function ($pipeline, $value) use (&$applyPipeline, $query, $field) {
      if (empty($pipeline)) return $query;
      if ($value === null) return $query;

      list($filter) = $pipeline;
      $filter($query, $field, $value, fn ($value) => $applyPipeline(array_slice($pipeline, 1), $value));
      return $query;
    };

    return $applyPipeline($this->pipeline, $value);
  }

  private function equals($query, $field, $value) {
    $query->where($field, $value);
  }

  private function like($query, $field, $value) {
    $query->where($field, 'like', "%$value%");
  }

  private function contains($query, $field, $value) {
    $query->whereNested(fn ($q) => $q->whereJsonContains($field, $value));
  }

  private function gt($query, $field, $value) {
    $query->where($field, '>', $value);
  }

  private function lt($query, $field, $value) {
    $query->where($field, '<', $value);
  }

  private function gte($query, $field, $value) {
    $query->where($field, '>=', $value);
  }

  private function lte($query, $field, $value) {
    $query->where($field, '<=', $value);
  }

  private function for_each($query, $field, $value, $next) {
    $values = array_map('trim', explode(',', $value));
    foreach ($values as $value) {
      $next($value);
    }
  }

  private function boolean($query, $field, $value, $next) {
    $next((boolean) $value);
  }
}
