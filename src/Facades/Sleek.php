<?php namespace Prometa\Sleek\Facades;

/**
 * @method static void raise(string $message, $type = 'info')
 * @method static static|\Prometa\Sleek\Views\Builder\SleekLanguageBuilder language(callable | array $data = null)
 * @method static static|\Prometa\Sleek\Views\Builder\SleekAuthenticationBuilder authentication(callable | array | false $data = null)
 * @method static static|\Prometa\Sleek\Views\Builder\SleekMenuBuilder menu(callable | array $data = null)
 * @method static static|\Prometa\Sleek\Views\Builder\SleekThemeBuilder theme(callable | array $data = null)
 * @method static static|\Prometa\Sleek\Views\Builder\SleekAssetsBuilder assets(callable | array $data = null)
 * @method static static|\Prometa\Sleek\Views\Builder\SleekAlertBuilder alert(callable | array $data = null)
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
