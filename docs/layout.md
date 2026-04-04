# Page Layout

Sleek features a dynamic page layout system, offering a strong default while staying configurable. Simply use the
`sleek::view` component to get a complete html-page, assets, menu and scaffolding included!

```blade
<x-sleek::view>
    <div>Your page goes here</div>
</x-sleek::view>
```

### Defining Assets

Assets are defined via the Sleek-Facade in your ServiceProvider:

```php
Sleek::assets([
    'vite' => [ /* Your vite bundles go here */ ],
    /* additional dependencies go here */
]);
```

### Defining the Menu Structure

Menu items can be defined on 2 levels. On the one hand, you can define menu items via the Sleek-Facade:

```php
Sleek::menu([
    [
        'route' => route('index'),
        'label' => __('navbar.index'),
    ],
    [
        /* Enables flexible icon usage via the `icon` attribute:
         * 
         * - Simple Bootstrap icon (e.g., 'people'): 'people' will use the 'bi-people' class on the icon tag.
         * - Explicit Bootstrap icon with 'bi:' prefix (e.g., 'bi:people'): 'bi-people' class on the icon tag.
         * - Blade component with 'component:' prefix (e.g., 'component:my-icon'): will use the component 'my-icon'.
         *   It's also possible to use namespaced components (e.g., 'my-icons::my-icon')
         */
        'icon' => 'people',
        'route' => 'routes('customers'),
        'label' => __('navbar.customers'),
    ],
    [
        'label' => __('navbar.settings'),
        /* Navigation items can be nested. Items have the same structure as top-level items.
         * As of now, only one level of nesting is supported.
         */
        'items' => [
            'route' => route('settings.general'),
            'label' => __('navbar.general'),
        ]
    ],
])
```

Since the service provider is executed outside of a request-context, authentication information an the like are not
available. To circumvent this problem, all methods on the Sleek-Facade also accept a callback. Sleek will use the
DI-Container for the callback's parameters and will execute it when the view is rendered.

```php
Sleek::menu(fn () => [
    [
        'route' => route('index'),
        'label' => __('navbar.index'),
    ],
])
```

For one-off changes to the navigation structure, items can also be defined on the `sleek::view` component:

```blade
<x-sleek::view :page:nav:items="[
    /* The same structure as above is valid here */
]">
</x-sleek::view>
```

This is especially powerful if you need to define sub-sections of your applications which need a different
navigation structure. You can do this by defining your own view component, based on `sleek::view`:

```blade
{{-- resources/components/custom-view.blade.php --}}
<x-sleek::view {{ $attributes->merge([
    'page:nav:items' => [
        /* Menu structure goes here */
    ]
], false) }}>
    {{ $slot }}
</x-sleek::view>

{{-- resources/components/nested/page.blade.php --}}
<x-custom-view>
    Neseted page comes here
</x-custom-view>
```

### Authentication

By default, the navbar shows Login/Logout actions for the user, using the standard `login` and `logout` route names.
You can customize these routes using the Sleek-Facade:

```php
Sleek::authentication(['login' => '/custom-login', 'logout' => '/custom-logout']);
```

If you do not need authentication routes, you can deactivate it as follows.

```php
Sleek::authentication(false);
```

### Language Switcher

The package has a built-in language switcher. You only need to define the available languages in the AppServiceProvider. For example:

```php
Sleek::language(['de' => 'Deutsch', 'en' => 'Englisch']);
```

### Theme Configuration

Sleek allows you to configure Bootstrap theme colors to match your application's branding.

```php
Sleek::theme([
    'colors' => [
        'primary' => '#007bff',
        'secondary' => '#6c757d',
        'success' => '#28a745',
        // ...
    ]
]);
```

Bootstrap itself is primarily a Sass-based system, but Sleek injects theming at runtime via plain CSS. 
To make this work seamlessly, Sleek overrides the relevant Bootstrap styles to reference Bootstrap's CSS variables 
(like the `--bs-*` tokens) and relies on native CSS capabilities (variables, color functions and transforms) 
rather than recompiling Sass. This effort is a work in progress and only encompasses select bootstrap classes so far.
Feel free to open a pull request or contribute to the project if you need more classes.
