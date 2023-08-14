<?php namespace Prometa\Sleek;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

if (! function_exists('resolveKeyFromContext')) {
    function resolveKeyFromContext(Model $model = null) {
        if ($model) {
            return str_replace('_', '-', Str::snake($model->getTable(), '-'));
        }

        $currentRoute = Route::getCurrentRoute();
        if ($routeName = $currentRoute->getName()) {
            return substr($routeName, 0, strrpos($routeName, '.') ?: strlen($routeName));
        }

        if (($action = $currentRoute->getActionName()) != "Closure") {
            $controllerClassName = explode('@', $action)[0];
            $controllerName = array_slice(explode('\\', $controllerClassName), -1)[0];
            return Str::snake(str_replace('Controller', '', $controllerName), '-');
        }
    }
}
