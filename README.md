# Sleek Laravel Package

Sleek is a Laravel package that provides a variety of useful features for your Laravel application. All components are styled with Bootstrap for a cohesive and attractive design. The package comes with Bootstrap UI components featuring aggressive defaults.

## Table of Contents
1. [Installation](#installation)
2. [Page Layout](#page-layout)
3. [UI Components](#ui-components)

## Installation

To get started, install Sleek via the Composer package manager:

```bash
composer require prometa/sleek
```

## Page Layout
### Layout
#### Creating a Simple Layout

With this package, you can easily create a simple layout for your application. First, create your own layout file and include the following code:

```blade
@extends('sleek::layouts.page')

@push('assets')
    @vite(['resources/js/app.js'])
@endpush
```
#### Including Assets
The `assets` section allows you to include any required assets for your layout.

#### Using Your Custom Layout
Once your custom layout is created, you can use it in all of your Blade files like so:
```html
@extends('layouts.app')

@section('body')
    <your html/blade>
@endsection
```
### Navbar Configuration

The package comes with a simple, yet configurable, navbar that supports features like Login/Logout and a language selector. You can configure it to better suit the needs of your application.

#### Login/Lougut
By default, the routes named Login and Logout are used. You can define the routes in the `AppServiceProvider` as follows.
```php
Sleek::authentication(['login' => '/login', 'logout' => '/logout']);
```
#### Navbar Elements
To insert elements in the menu bar, this can also be done in the `AppServiceProvider`.
```php
Sleek::menu([['route' => '/users', 'label' => 'Benutzer']]);
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
The Entity Form feature allows you to easily create forms for editing existing entities and create forms. Instead of manually specifying each field and its value, you can simply pass the entity and the list of fields to display. The CSRF Token will be set automatically. You can also set the Method to PUT, POST, DELETE, GET
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
#### Usage
You can either use the `form-field` components to create fields. The most common types are supported. It is also possible to create fields with normal HTML and use the form component only as a help for the CSRF token and the method
```html
<x-sleek::form method="PUT" action="{{route('user.update',$user->id)}}">
    <x-sleek::form-field name="desc"></x-sleek::form-field>
    <button type="submit" class="btn btn-primary">{{__('messages.assign_import')}}</button>
</x-sleek::form>
```

It is also possible to create select fields.
```html
<x-sleek::form-field name="user" type="select" >
    @foreach($users as $user)
        <option value="{{$user->id}}">{{$user->name}}</option>
    @endforeach
</x-sleek::form-field>
```
