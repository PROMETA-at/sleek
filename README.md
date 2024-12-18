# Sleek Laravel Package

Sleek is a Laravel package that provides a variety of useful features for your Laravel application. All components are styled with Bootstrap for a cohesive and attractive design. The package comes with Bootstrap UI components featuring aggressive defaults.

## Table of Contents

- [Installation](#installation)
- [Requirements](#requirements)
- [Page Layout](#page-layout)
  - [Layout](#layout)
  - [Navbar-Config](#navbar-configuration)
- [UI Components](#ui-components)
  - [Alert](#alert)
  - [Entity-Table](#entity-table)
  - [Entity-Form](#entity-form)
  - [Form](#form)
  - [Modal-Form](#modal-form)
- [Example](#example)

## Installation

To get started, install Sleek via the Composer package manager:

```bash
composer require prometa/sleek
```

After installation, the service provider must be registered. To do this, the following must be entered in the `providers` array at `config/app.php`

```bash
\Prometa\Sleek\Providers\SleekServiceProvider::class,
```

Sleek offers a setup command to automatically install and set up the necessary dependencies. The `sleek:setup` command
will check your bootstrap and bootstrap-icons installations and inject an import to sleek's sass into your app.scss:

```bash
php artisan sleek:setup
```

## Page Layout

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
        /* Icon names are assumed to be bootstrap icon classes. For example,
         * 'people' will use the 'bi-people' class on the icon tag.
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

## UI Components

### Alert

#### Usage

If you use the Sleek layout, the alert is automatically included and ready to use. Otherwise you
can add it to your own layout or to an individual page.

```html
<x-sleek::alert />
```

After that you can use and trigger the alert in your controller.

```php
use Prometa\Sleek\Facades\Sleek;

Sleek::raise('Your message goes here', 'danger');
```

The first parameter is the message and the second is the type.
The icons displayed are dependent on the type.

The following types are supported:

- `danger`
- `warning`
- `info`
- `success`
- `primary`
- `secondary`
- `light`
- `dark`

You can set the position of the alert in the `AppServiceProvider`.

```php
Sleek::alert([
    'position' => 'top-right'
]);
```

Available positions: `center`, `top-left`, `top-right`, `bottom`, `bottom-left`, `bottom-right`

### Entity-Table

You can easily create a table with data using the `entity-table` component. The table also supports pagination out-of-the-box.

Here's a basic example:

```html
<x-sleek::entity-table
    :entities="$users"
    :columns="['name', 'email', 'actions']"
/ >
```

#### Parameters

- `entities`: Specifies the collection that should be displayed in the table.
- `columns`: Defines which fields from the collection should be displayed in the table. Note that you can include fields that are not in the entities collection, such as `actions`, to customize your table further.

In the above example, the `entities` parameter is set to `$users`, which means the table will display data from the `$users` collection. The `columns` parameter specifies that the fields `'name', 'email', 'actions'` will be shown.

#### Customize the Table

You can further customise each field of the table, e.g. by adjusting the formatting in each table cell using slots, or you can display other fields.

#### Customizing Cell Format

It is important that the name of the column is used in the slot.
`<x-slot:column-<columnName> />`

To customize the format of a specific column, you can use the slot with the column name. This is very useful for date fields to display in the format of your region. Here's an example that customizes the `name` column:

```html
<x-slot:column-name bind="$name">
    User: {$name}
</x-slot:column-name>
```

This changes the way names are displayed in the `name` column

#### Accessing Full Model Data

You can also pass the entire model as an argument to the slot to have access to all of its fields. This can be especially useful for incorporating model IDs into routes for actions like deleting an entry. Here's how you can do it:

```html
<x-slot:column-actions bind="$_,$user">
    {$user->name} {$user->id}
    <a href="{{route('users.show',$user->id)}}">Details</a>
</x-slot:column-actions>
```

As you can see, this allows you to access fields that may not even be displayed in the table, such as the model's ID.

### Entity-Form

The Entity Form feature allows you to easily create forms for editing existing entities and create forms. Instead of manually specifying each field and its value, you can simply pass the entity and the list of fields to display. The CSRF Token will be set automatically. You can also set the Method to `PUT`, `POST`, `DELETE`, `GET`

```html
<x-sleek::entity-form :fields="['name' , 'email']">
    <button type="submit" class="btn btn-primary">Submit</button>
</x-sleek::entity-form>
```

This example would create a form with two field name and email.

#### Customize Route

You can easily change the route of the form by defining the action.

```html
<x-sleek::entity-form action="{{route('profil.update',$user)}}"
    :fields="['name','email']"
>
    <button type="submit" class="btn btn-primary">Submit</button>
</x-sleek::entity-form>
```

#### Use a model

If you give the model attribute a user model, for example, then the fields are automatically filled with the existing data. This way you can easily develop e.g. a user edit page.

```html
<x-sleek::entity-form action="{{route('profil.update',$user)}}"
    :model="$user"
    :fields="['name','email']"
>
    <button type="submit" class="btn btn-primary">Submit</button>
</x-sleek::entity-form>
```

## Forms

Sleek attempts to make writing forms as easy as possible. Beyond components for defining and managing forms, sleek
also provides natural shortcuts for defining forms upon model instances.

### Defining a Form

Sleek provides the `sleek::form` component to define a standard HTML-Form. Beyond being a normal form element, this
form will automatically handling the methods for 'PUT', 'PATCH' and 'DELETE' and automatically insert the CSRF-Token
when applicable:

```blade
{{-- This form uses method="POST" by default, as it's way more useful than a method="GET" default --}}
<x-sleek::form :action="route('some.route')"></x-sleek::form>

{{-- This form will automatically transform to use method="POST" 
     and inject @method('PUT') and @csrf into it's body. --}}
<x-sleek::form method="PUT" :action="route('some.put.route')"></x-sleek::form>

{{-- If you still need a method="GET" form, you just need to explicitly say so --}}
<x-sleek::form method="GET" :action="route('some.get.route')"></x-sleek::form>
```

### Defining Form Fields

To define a form field, sleek provides the `sleek::form-field` component. This component wraps the standard HTML-Input,
-Select and -Textarea fields (an does a whole lot more magic behind the scenes), allowing you to define form fields
with one unified component:

```blade
{{-- Will use the standard <input type="text"> --}}
<x-sleek::form-field name="first-field" />

{{-- Will use the standard <textarea></textarea> --}}
<x-sleek::form-field type="textarea" name="textarea-fields" />

{{-- All of them will forward their type to <input>-Element --}}
<x-sleek::form-field type="checkbox" />
<x-sleek::form-field type="number" />
<x-sleek::form-field type="date" />
<x-sleek::form-field type="datetime" />
<x-sleek::form-field type="hidden" />

{{-- Will render a <select>-Element --}}
<x-sleek::form-field type="select">
    <option value="1">One</option>
    <option value="2">Two</option>
    <option value="3">Three</option>
</select>
<x-sleek::form-field type="select" :options="['1' => 'One', '2', => 'Two', 3 => 'Three']" />

{{-- Will render a list of radio buttons --}}
<x-sleek::form-field type="radio-group" :options="['1' => 'One', '2', => 'Two', 3 => 'Three']" />
```

### Field Names

Like normal form fields, every field needs a name. Beyond just setting a name, `sleek::form-field` will automagically
convert dot-notation to nested field names and automatically append `[]` for multi-selects.

```blade
<x-sleek::form-field name="the-name" />

{{-- name will be "nested[name]" --}}
<x-sleek::form-field name="nested.name" />

{{-- name will be "multi-select[]" --}}
<x-sleek::form-field type="select" multiple name="multi-select">{{-- ... --}}</x-sleek::form-field>
```

### Field Labels

Form fields include labels in their rendered output.
By default, it will try to be smart and guess an appropriate translation key for the label, 
based on the current route's name.

For example, if the current route is named 'users.show' (standard naming convention when using Resource-Controllers),
the translation key will be set to 'users.fields.<name>'.

Generally, the form field takes the route, strips the last segment from it and uses the rest as a prefix for the
translation key: '<prefix>.fields.<name>'.

As long as you adhere to standard naming and framework conventions, you should generally have no need to set the
label explicitly. However, if you need to, there are several ways override the standard guessing logic, depending
on the needed granularity.

#### Setting an Explicit Label

Of course you can just set an explicit label. `sleek::form-field` both accepts the label as an attribute and slot:
```blade
<x-sleek::form-field label="Custom Label" />

<x-sleek::form-field>
    <x-slot:label>
        Custom Label
    </x-slot:label>
</x-sleek::form-field>
```

#### Setting a Custom Prefix

If you only need a custom prefix to find the correct translation file, you can use the `i18nPrefix` property:

```blade
{{-- will resolve the label from 'custom.prefix.fields.the-field' --}}
<x-sleek::form-field i18nPrefix="custom.prefix" name="the-field" />
```

This property is also checked on any parent component, making it especially useful to define on form components,
meaning all containing fields will use the custom prefix:

```blade
<x-sleek::form :action="route('some.route')" i18nPrefix="custom.prefix">
    {{-- translation key: 'custom.prefix.first-field' --}}
    <x-sleek::form-field name="first-field" />
    {{-- translation key: 'custom.prefix.second-field' --}}
    <x-sleek::form-field name="second-field" />
</x-sleek::form>
```

### Field values

Beyond setting a value directly, `sleek::form-field` will automatically resolve the old input value from the session 
upon re-render (for example when validation fails).

The value will be used appropriately depending on the input type, so you don't have to manage "checked" or "selected"
attributes manually.

```blade
<x-sleek::form-field name="field-name" value="the value" />
{{-- Renders as: --}}
<input name="field-name" value="the value">

<x-sleek::form-field name="checkbox-field" type="checkbox" :value="true" />
{{-- Renders as: --}}
<input type="checkbox" name="checkbox-field" checked>

<x-sleek::form-field name="select-field" type="select" value="1" :options="['1' => 'One', '2' => 'Two']" />
{{-- Renders as: --}}
<select name="select-field">
    <option value="1" selected>One</option>
    <option value="2">One</option>
</select>
```

## Entity Forms

While the form and field components are already useful by themselves, Sleek also provides a `sleek::entity-form`
component, which can automatically build a form from a model!

```blade
<x-sleek::entity-form 
    :model="$user"
    :fields="[
        /* Assumed type="text" */
        'name',
        /* Explicit name => type combination */
        'is_admin' => 'checkbox',
        /* Verbose definition */
        'roles' => [
            'type' => 'select',
            'attributes' => ['multiple'],
        ],
    ]"
/>
```

The above declaration autmagically sets up a few things:

- Since we pass in a model the form assumes an update operation and sets the method to 'PUT'
- The form automatically assumes an "action" property based on the current route.
- The form creates form fields from the "fields" attribute.
- The form pulls out the field values from the given model.

Lets go through them one by one:

### Form method Guessing

Depending on if you provide a model or not, the method of the form will be set to 'POST' or 'PUT' respectively.
This aligns with standard assumptions about the routes for creating and updating resources in RESTful APIs.

```blade
{{-- Will set method="POST" --}}
<x-sleek::entity-form />

{{-- Will set method="PUT" --}}
<x-sleek::entity-form :model="$user" />

{{-- Explicitly setting the method overrides automatic guessing, so method="PATCH" --}}
<x-sleek::entity-form :model="$user" method="PATCH" />
```

### Form Action Guessing

Depending on the resolved form method, an appropriate form action will be set. Sleek assumes standard form names for
ResourceControllers and tries to resolve the route based on those route names. 

As with translation keys for form fields, the current route name is used as a basis for constructing the route name.
For example, if the current route is named `users.edit` and the method="PUT", the action will be resolved to the route
named `users.update`.
Alternatively, if the current route is named `users.create` and the method="POST", the action will be resolved to the
route named `users.store`.

Generally, as before, the form takes the route, strips the last segment from it and uses the rest as a prefix for the
route name: '<prefix>.update' or '<prefix>.store'.

```blade
{{-- --}}
<x-sleek::entity-form method="POST" />

{{-- Will set method="PUT" --}}
<x-sleek::entity-form method="PUT" />

{{-- Explicitly setting an action overridese automatic guessing --}}
<x-sleek::entity-form :action="route('custom.action')" />
```

### Modal Form

This component can be used to create a form in a dialogue. The form also uses Alpine.js to deactivate the button after the submit and display a loading spinner.

#### Usage

```html
<button data-bs-target="#add-user-modal" data-bs-toggle="modal">User Create</button>
<x-sleek::modal-form
    title="User"
    :action="route('users.store')"
    method="POST"
    id="add-user-modal"
>
    <x-sleek::form-field type="text" name="name" label="Name"/>
</x-sleek::modal-form>
```

The attributes in the modal form are required. The button to open the dialogue must be linked to the ID of the dialogue.
To define fields, it is best to use the form-field component.

#### Customization

If you want to change the text of the buttons in the dialogue, you can do this as follows.

```html
<button data-bs-target="#add-user-modal" data-bs-toggle="modal">User Create</button>
<x-sleek::modal-form
    title="User"
    :action="route('users.store')"
    method="POST"
    id="add-user-modal"
>
    <x-slot:submit label="Submit" ></x-slot:submit>
    <x-slot:cancel label="Cancel" ></x-slot:cancel>
</x-sleek::modal-form>
```

## Example
Here you can find a full CRUD example [Show Code](./EXAMPLE.md)
