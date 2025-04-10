<?php namespace Prometa\Sleek\Tabs;

class TabsContext
{
    public function __construct(
        public readonly string $keyField = 'tab',
    ) {

    }

    private static self $defaultInstance;
    public static function default(): self
    {
        return self::$defaultInstance ??= new self();
    }
}