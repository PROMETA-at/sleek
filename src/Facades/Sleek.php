<?php namespace Prometa\Sleek\Facades;

/**
 * @method static void raise(string $message, $type = 'info')
 * @method static static language(callable | array $data)
 * @method static static authentication(callable | array | false $data)
 * @method static static menu(callable | array $data)
 * @method static static theme(callable | array $data)
 * @method static static assets(callable | array $data)
 * @method static static alert(callable | array $data)
 */
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
