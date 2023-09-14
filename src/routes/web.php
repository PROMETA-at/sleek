<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use Prometa\Sleek\Middleware\LocaleMiddleware;

Route::get('lang/{locale}', function ($locale) {
    session()->put('locale', $locale);
    App::setLocale($locale);
    return redirect()->back();
});
