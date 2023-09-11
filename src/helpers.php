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
