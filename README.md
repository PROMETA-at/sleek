# Sleek Laravel Package

Sleek is a Laravel package that provides a variety of useful features for your Laravel application. All components are styled with Bootstrap for a cohesive and attractive design. The package comes with Bootstrap UI components featuring aggressive defaults.

## Table of Contents

- [Installation](#installation)
- [Page Layout](#page-layout)
  - [Defining Assets](#defining-assets)
  - [Defining the Menu Structure](#defining-the-menu-structure)
  - [Authentication](#authentication)
  - [Language Switcher](#language-switcher)
- [Forms](#forms)
  - [Defining a Form](#defining-a-form)
  - [Defining Form Fields](#defining-form-fields)
  - [Field Names](#field-names)
  - [Field Labels](#field-labels)
  - [Grouping Form Fields](#grouping-form-fields)
- [Entity Forms](#entity-forms)
  - [Form Method Guessing](#form-method-guessing)
  - [Form Action Guessing](#form-action-guessing)
  - [Form Field Generation](#form-field-generation)
  - [Value Extraction](#value-extraction)
  - [Using Form Groups in Entity-Forms](#using-form-groups-in-entity-forms)
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

### Grouping Form Fields

Sometimes, you want to group fields together, so they form a nested attribute in the submit payload. For example, 
the following form would group otherwise redundant names into a nested attribute:

```blade
<x-sleek::form>
    <x-sleek::form-field name="name" />
    <x-sleek::form-field name="address" />

    <x-sleek::form-field name="contact.name" />
    <x-sleek::form-field name="contact.address" />
</x-sleek::form>
```

When laravel parses the payload from submitting the above form, it would look like this:
```php
[
    'name' => '...',
    'address' => '...',
    'contact' => [
        'name' => '...',
        'address' => '...'
    ],
]
```

However, having to specify the group key each time can be redundant and increase the complexity of composite forms. To
combat this problem, you can simply wrap fields in a `sleek::form-group`:

```blade
<x-sleek::form-group name="contact">
    {{-- Will have name="contact[name]" --}}
    <x-sleek::form-field name="name" />
    {{-- Will have name="contact[address]" --}}
    <x-sleek::form-field name="address" />
</x-sleek::form-group>
```

The form group is also a nice place to set a custom `i18nPrefix` if you need to change the lookup for a subset of
form fields:

```blade
<x-sleek::form-group i18nPrefix="contacts">
    {{-- Will use the translation key "contacts.fields.name" --}}
    <x-sleek::form-field name="name" />
    {{-- Will use the translation key "contacts.fields.address" --}}
    <x-sleek::form-field name="address" />
</x-sleek::form-group>
```

#### Nesting Form Groups

Form groups nest properly! So you can do crazy stuff like the following when you find the need to:

```blade
<x-sleek::form-group name="contact">
    <x-sleek::form-group name="address">
        {{-- Will have name="contact[address][street]" --}}
        <x-sleek::form-field name="street" />
    </x-sleek::form-group>
</x-sleek::form-group>
```

While the above example is pretty contrived, this behavior allows you to compose forms from multiple specialized
components without thinking about it too much. Imagine the following component:

```blade
{{-- contacts/form.blade.php --}}
@props(['name' => null])

<x-sleek::form-group :name="$name">
    <x-sleek::form-field name="name" />
    <x-sleek::form-field name="address" />
</x-sleek::form-group>
```

We can now use this component in other forms and ensure the data nests properly, allowing for easy reuse:
```blade
{{-- users/create.blade.php --}}
<x-sleek::form>
    <x-sleek::form-field name="name" />
    <x-contacts.form name="contact" />
</x-sleek::form>
```

#### A Note on From Group Markup

Currently, the Form Group simply renders it's slot, not adding any Markup in the process. We're keeping the option open
to add sensible markup to actually *group* form elements visibly, but if you want to be absolutely sure that
`sleek::form-group` only groups logically and does not add markup, add the `passthrough` property to the component:

```blade
<x-sleek::form-group passthrough>
    I will <strong>never</strong> be wrapped in markup!
</x-sleek::form-group>
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
            /* additional properties */
            'multiple',
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

### Form Method Guessing

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

### Form Field Generation

Under the hood, `sleek::entity-form` will use `sleek::form-field`s under the hood to convert the `fields` property
into form elements, so the same magic for it's properties apply here. The array accepts a few forms, depending on
your required level of detail:

```blade
<x-sleek::entity-form
    :fields="[
        '<name>',
        '<name>' => '<type>',
        '<name>' => [
            'type' => '<type>',
            /* additional properties for the form-field go here */
        ],
    ]"
/>
```

The form fields generated through these methods are simply rendered in a straight down without any complex styling, so
this method is ideal for quick and simple forms. For more complex layouts you can still use `sleek::form-field` inside
of `sleek::entity-form` without any problem:

```blade
<x-sleek::entity-form :model="$user">
    <div style="display: grid; grid-template-columns: repeat(2, 1fr);">
        <x-sleek::form-field name="first-name" />
        <x-sleek::form-field name="last-name" />
    </div>
</x-sleek::entity-form>
```

These form fields will automagically be aware of the model passed to `sleek::entity-form`, which brings us to the last
part:

### Value extraction

Not strictly a feature of `sleek::entity-form` (the implementation actually lives in `sleek::form-field`), when a model
is passed to the form component, the current value for each form field will be extracted from the model and set on the
form control.

```blade
<x-sleek::entity-form 
    :model="new User(['name' => 'James Bond', 'active' => true, 'role' => 'special-agent'])"
>
    {{-- will have the property value="James Bond" --}}
    <x-sleek::form-field name="name" />

    {{-- will have the property "checked" --}}
    <x-sleek::form-field name="active" type="checkbox" />

    {{-- option with value "special-agent" will have property "selected" --}}
    <x-sleek::form-field name="role" type="select" :options="[/* ... */]" />
</x-sleek::entity-form>
```

When the data on your model is nested for any reason (relations, json columns, complex casts that create nested fields),
you can use dot-notation to access these nested values:

```blade
<x-sleek::entity-form :model="new User(['tenant' => ['name' => 'FBI']])">
    {{-- will have the properties name="tenant[name]" and value="FBI" --}}
    <x-sleek::form-field name="tenant.name" />
</x-sleek::entity-form>
```

As you can see, the name property is used both for setting the input's name and fetching the value from the model. If
you need a different way to access the value, you can also set the `accessor` property on `sleek::form-field` resolve
the value. The same dot-notation syntax is supported here:


```blade
<x-sleek::entity-form :model="new User(['tenant' => ['name' => 'FBI']])">
    {{-- will have the properties name="tenant-name" and value="FBI" --}}
    <x-sleek::form-field name="tenant-name" accessor="tenant.name" />
</x-sleek::entity-form>
```

### Using Form Groups in Entity-Forms

Form groups are especially powerful in Entity-Forms! As discussed previously, `sleek::form-group` can set common
prefixes for multiple field names and as discussed just above, field names are by default used to extract current
values from models.

This synergy makes form groups very convenient when you need form fields for nested data:
```blade
<x-sleek::entity-form :model="new User(['tenant' => 'name' => 'FBI'])">
    <x-sleek::form-group name="tenant">
        {{-- will have the properties name="tenant[name]" and value="FBI" --}}
        <x-sleek::form-field name="name" />
    </x-sleek::form-group>
</x-sleek::entity-form>
```

This means that it's often a good idea to let your form structure mirror your model's data structure, which also helps
when filling the submitted form values back into the model on update.

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

## Modal Form

This component can be used to create a form in a dialogue. The form also uses Alpine.js to deactivate the button after the submit and display a loading spinner.

### Usage

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

### Customization

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
