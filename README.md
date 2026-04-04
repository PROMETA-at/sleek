# Sleek Laravel Package

Sleek is a Laravel package that provides Bootstrap UI components with aggressive defaults for rapid Laravel development. Every behavior defaults to the most likely use case, while remaining fully customizable.

## Installation

To get started, install Sleek via the Composer package manager:

```bash
composer require prometa/sleek
```

By default, the service provider is automatically registered via [Laravel's package auto-discovery](https://laravel.com/docs/master/packages#package-discovery). No additional steps are required.

However, if auto-discovery is disabled or does not work as expected, you can register the service provider manually. To do this, add the following line to the `providers` array in your `config/app.php` file:

```bash
\Prometa\Sleek\Providers\SleekServiceProvider::class,
```

Sleek offers a setup command to automatically install and set up the necessary dependencies. The `sleek:setup` command will check your bootstrap and bootstrap-icons installations and inject an import to sleek's sass into your app.scss:

```bash
php artisan sleek:setup
```

## Documentation

- [Page Layout](docs/layout.md) — page scaffolding, assets, menu, authentication, language, theme
- [Navigation](docs/navigation.md) — breadcrumbs
- [Forms](docs/forms.md) — form, form fields, form groups, entity forms, value extraction
- [Tables](docs/tables.md) — entity tables, sorting, pagination, custom columns
- [Eloquent Extensions](docs/eloquent.md) — autoSort, autoPaginate, autoFilter
- [UI Components](docs/components.md) — icon, alert, card
- [Modals](docs/modals.md) — modal, modal-form
- [Tabs](docs/tabs.md) — presets, headless component, HTMX integration
- [Blade Directives](docs/directives.md) — @capture/@into, @flags/@flag, @forwardSlots, @ensureSlotFor
- [Implicit Behaviors](docs/implicit-behaviors.md) — quick-reference for all convention-based magic
