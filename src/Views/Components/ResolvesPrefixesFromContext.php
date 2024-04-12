<?php

namespace Prometa\Sleek\Views\Components;

use Illuminate\Database\Eloquent\Model;
use function Prometa\Sleek\resolveI18nPrefixFromModel;
use function Prometa\Sleek\resolveKeyFromContext;

trait ResolvesPrefixesFromContext
{
    public ?string $key;
    public ?string $i18nPrefix;
    public ?string $routePrefix;

    private function resolvePrefixesFromContext($model = null) {
        // The key is used to automagically resolve translation entries and routes for detail and edit views.
        //  If not set, we try to resolve a reasonable key from the current route name.
        //  We do not pass the model here, because we need a proper key including routing information.
        if (! $this->key) {
            $this->key = resolveKeyFromContext();
        }

        $this->i18nPrefix =
            $this->i18nPrefix ??
            static::factory()->getConsumableComponentData('i18nPrefix') ??
            $this->key ??
            resolveI18nPrefixFromModel($model) ??
            array_slice(explode('.', $this->key), -1)[0];
        $this->routePrefix =
            $this->routePrefix ??
            static::factory()->getConsumableComponentData('routePrefix') ??
            $this->key ??
            // If all else fails, falling back to the i18nPrefix for the routePrefix is an OK bet.
            resolveI18nPrefixFromModel($model);
    }
}
