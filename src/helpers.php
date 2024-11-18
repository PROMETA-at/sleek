<?php namespace Prometa\Sleek;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('resolveKeyFromContext')) {
    function resolveKeyFromContext() {
        $currentRoute = Route::getCurrentRoute();
        if ($routeName = $currentRoute->getName()) {
            return substr($routeName, 0, strrpos($routeName, '.') ?: strlen($routeName));
        }
    }
}

if (! function_exists('resolveI18nPrefixFromModel')) {
    function resolveI18nPrefixFromModel($model = null) {
        if ($model instanceof Model)
            return str_replace('_', '-', Str::snake($model->getTable(), '-'));
        return null;
    }
}

if (! function_exists('array_merge_recursive_distinct')) {
    /**
     * @param array<int|string, mixed> $array1
     * @param array<int|string, mixed> $array2
     *
     * @return array<int|string, mixed>
     */
    function array_merge_recursive_distinct(array &$array1, array &$array2): array
    {
        $merged = $array1;
        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }
}

if (! function_exists('as_parameter_name')) {
  /**
   * Convert a dotted string to an HTML-Parameter-friendly name.
   *
   * This function converts a dotted name like `nested.property` into a name that's understood by Laravel's
   * paramter resoultion mechanism, which is `nested[property]`.
   */
  function as_parameter_name(string $dotted): string {
    return with(
      explode('.', $dotted), 
      fn ($parts) => $parts[0] . implode('', array_map(fn ($part) => "[$part]", array_slice($parts, 1)))
    );
  }
}
