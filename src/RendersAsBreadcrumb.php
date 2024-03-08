<?php namespace Prometa\Sleek;

use Illuminate\View\View;

interface RendersAsBreadcrumb
{
  public function asBreadCrumb(): string|View;
}
