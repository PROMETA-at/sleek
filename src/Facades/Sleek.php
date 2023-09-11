<?php namespace Prometa\Sleek\Facades;

class Sleek extends \Illuminate\Support\Facades\Facade
{
  /**
   * Get the registered name of the component.
   */
  protected static function getFacadeAccessor(): string
  {
    return 'sleek';
  }
}
