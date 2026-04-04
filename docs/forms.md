# Forms

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
<x-sleek::form-field type="radio-group" :options="['1' => 'One', '2' => 'Two', 3 => 'Three']" />
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
{{-- Will use route named 'users.store' --}}
<x-sleek::entity-form method="POST" />

{{-- Will use route named 'users.update' --}}
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
