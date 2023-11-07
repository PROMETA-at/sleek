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

## Installation

To get started, install Sleek via the Composer package manager:

```bash
composer require prometa/sleek
```

After installation, the service provider must be registered. To do this, the following must be entered in the `providers` array at `config/app.php`

```bash
\Prometa\Sleek\Providers\SleekServiceProvider::class,
```

## Requirements

The following things are required to use all functions

- Bootstrap 5
- Bootstrap Icons
- Alpine.js
- HTMX

## Page Layout

### Layout

#### Creating a Simple Layout

With this package, you can easily create a simple layout for your application. First, create your own layout file and include the following code:

```blade
@extends('sleek::layouts.page')

@push('assets')
    //You can use this as a quickstart template
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.6" integrity="sha384-FhXw7b6AlE/jyjlZH5iHa/tTe9EpJ1Y55RjcgPbjeWMskSxZt1v9qkxLJWNJaGni" crossorigin="anonymous"></script>
@endpush
```

#### Including Assets

The `assets` section allows you to include any required assets for your layout.

#### Using Your Custom Layout

Once your custom layout is created, you can use it in all of your Blade files like so:

```html
@extends('layouts.app')

@section('body')
    //your html/blade
@endsection
```

### Navbar Configuration

The package comes with a simple, yet configurable, navbar that supports features like Login/Logout and a language selector. You can configure it to better suit the needs of your application.

#### Login/Lougut

By default, the routes named Login and Logout are used. You can define the routes in the `AppServiceProvider` as follows.

```php
Sleek::authentication(['login' => '/login', 'logout' => '/logout']);
```

If you do not need authentication routes, you can deactivate it as follows.

```php
Sleek::authentication(false);
```

#### Navbar Elements

To insert elements in the menu bar, this can also be done in the `AppServiceProvider`.

```php
Sleek::menu([['route' => '/users', 'label' => 'Benutzer']]);
```

It is also possible to pass a clousre, for example

```php
Sleek::menu(function () {
    if (Auth::check()) return [['route' => '/users', 'label' => 'Benutzer'], ['route' => '/tags', 'label' => 'Tags']];
    return [];
});
```

#### Language Switcher

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

### Form

The component helps to create a form. You can specify `PUT`, `POST`, `GET`, `DELETE` as mehtod and define an action. The CSRF token is set automatically.

#### Form-Field
... more soon

It is also possible to create select fields.

```html
<x-sleek::form-field name="user" type="select" >
    @foreach($users as $user)
        <option value="{{$user->id}}">{{$user->name}}</option>
    @endforeach
</x-sleek::form-field>
```

#### Form-Actions
With this component you can easily insert a Submit and Cancel button into a form. When using the Sleek Form, the Submit button is deactivated by Alpine.js and the spinner is displayed.
You can style the component as you wish and also change the label of the buttons.

```html
<x-sleek::form-actions class="text-end">
    <x-slot:submit label="Submit" ></x-slot:submit>
    <x-slot:cancel label="Cancel" ></x-slot:cancel>
</x-sleek::form-actions>
```
If you use the Sleek form, it depends on which method you use. Different buttons are displayed depending on the method. You can specify which buttons you want to see in the show array and access and change them via slots

```html
<x-sleek::form method="DELETE" action="{{route('users.destroy',compact('user'))}}">
      <x-sleek::form-actions />
</x-sleek::form>
```
The example above will create one Delete Button with a `confirm box`.

```html
<x-sleek::form-actions :show="['submit','reset','cancel']" />
```
The submit button is always displayed differently due to the method.

#### Usage

You can either use the `form-field` components to create fields. The most common types are supported. It is also possible to create fields with normal HTML and use the form component only as a help for the CSRF token and the method

```html
<x-sleek::form method="PUT" action="{{route('user.update',$user->id)}}">
    <x-sleek::form-field name="desc"></x-sleek::form-field>
    <button type="submit" class="btn btn-primary">{{__('messages.assign_import')}}</button>
</x-sleek::form>
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
