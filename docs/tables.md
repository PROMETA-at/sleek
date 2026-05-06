# Entity Tables

Just like `sleek::entity-form`, `sleek::entity-table` is a data-driven table component with much the same magic imbued.
Entity-Tables allow for very efficient creation of tables for a collection of models - sorting and pagination included!

Here's a simple example:
```blade
<x-sleek::entity-table
    :entities="new Paginator(
        collect([
            new User(['name' => 'Jon Doe', 'role' => 'user']),
            new User(['name' => 'Jon Bovi', 'role' => 'artist']),
        ]),
        pageSize: 1
    )"
    :columns="['name', 'role']"
    :sortable="true"
/>
```
(paginators are usually proveded by eloquent's query builder, we instantiate one directly in this example for clarity)

With this simple definition we get for free:

- Translated table headers
- Controls for sorting for each column
- Pagination controls
- A row for each model in the `entities` property, extracting the value for each column

There's a lot more to cover, but lets go through the above points for now:

### Table Header Generation

`sleek::entity-table` will use the provided `columns` array to generate the column headers for the table. The field
names will be used to generate translation keys, just like when using `sleek::form-field`. For example, if our current
route name is `users.show`, the `name`-column will look for a translation with the key `users.fields.name`.

As before, if you need a custom prefix for key generation, you can set `i18nPrefix` to use a custom prefix:
```blade
<x-sleek::entity-table
    :entities="[...]"
    i18nPrefix="custom-prefix"
    :columns="['name']"
/>
```
Here, the columns will use a prefix of `custom-prefix`, so the generated translation key will be 
`custom-prefix.fields.name`.

If you need to set a custom label for a single column, you can switch to the long form for column-definitions:
```blade
<x-sleek::entity-table
    :entities="[...]"
    :columns="['name' => ['label' => __('my.custom.label')]"
/>
```

### Sorting Controls

Setting the `sortable` property will append sorting controls to column headers. These controls are really just links to
the current page with additional parameters, so you still need to handle them on the server side.

> [!TIP]
> Check out our [auto sort helpers](eloquent.md#auto-sort) to automate handling of sorting parameters!

The `sortable` property either accepts a boolean, where `true` indicates all columns are sortable, or an array of field
names specifying the columns that are sortable.

There are 2 attributes that will be set:
- `sort-by` will identify the column to sort by, by name
- `sort-direction` will indicate the direction, either `asc` or `desc`

As a simple example, here's how you could use those in your Controller:
```php
User::query()
    ->when(request('sort-by'))
    ->orderBy(
        request('sort-by'),
        requres('sort-direction'),
    )
    ->get();
```

### Pagination Controls

Whenever the `entities` property holds an instance of either a `Paginator` or `CursorPaginator`, `sleek::entity-table`
will automatically render the pagination links from that paginator. Additionally to Laravel's default pagination
controls, we also added page size links.

By default, the pagination links are rendered both above *and* under the table. You can specify the location of the
pagination links by specifying the `navigation` property:

- `navigation="top"` will only render above the table
- `navigation="bottom"` will only render below the table
- `:navigation="false"` will disable pagination links

As with sorting links, pagination links are just links to the same page with additional parameters, so you need to
handle those on the server yourself.

> [!TIP]
> Check out our [auto paginate helpers](eloquent.md#auto-paginate) to automate handling of pagination parameters!

Again, there are 2 relevant attributes:
- `page` indicates the page that needs to be rendered
- `page-size` indicates the number of elements the page should hold

For example, here's how you could use those in your Controller:
```php
User::query()
    ->paginate(request('page-size'));
```

> [!NOTE]
> We do not supply the "page" since Laravel takes care of that internally.

### Pagination Window

Out of the box, the rendered pagination controls show a tight, balanced window of page buttons — first and last
pages pinned, a slider hugging the current page, and ellipses collapsing whatever's left over. For a 15-page list
on page 1 you get:

```
‹  1  2  3  4  5  6  7  8  …  14  15  ›
```

And on page 8:

```
‹  1  2  …  5  6  7  8  9  10  11  …  14  15  ›
```

Two knobs on the paginator shape this:

- `onEachSide` is Laravel's standard pagination slider — pages on either side of the current page (defaults to `3`).
- `borderWindowSize` is Sleek's addition — pages pinned at each edge of the list (defaults to `2`).

When the current page sits close enough to an edge that the slider would otherwise touch the border, the two
collapse into a single contiguous run that spans `2 * onEachSide + borderWindowSize` pages from that edge. That's
why page 1 of 15 shows `1..8` instead of just `1..4 … 14 15` — the slider absorbs the border and stretches forward
so the visible button count doesn't collapse as you near the edge.

For smaller tables you'll usually want to dial both down. Set them on the paginator before handing it to the table:

```php
$users = User::query()->autoPaginate()->onEachSide(1)->borderWindowSize(1);
```

That gives you a much tighter control — `1  2  3  …  15` near the edges and `1  …  3  4  5  …  15` in the middle.

### Scoping Parameter Names

Sometimes you might want to render multiple tables on the same page. While this can get complicated pretty quickly, with
multiple sets of sorting and pagination parameters, `sleek::entity-table` tries to simplify this as much as possible.

You can provide a `scoped` property to the component which will prefix all parameters this component appends to links:
```blade
<x-slot::entity-table
    :entities="[...]"
    :sortable="true"
    scoped="users"
/>
```

In this example, the above discussed parameters will instead be named:
- `users[sort-by]`
- `users[sort-direction]`
- `users[page]`
- `users[page-size]`

### Value Extraction

Just like `sleek::form-field`, `sleek::entity-table` will use the column name to automagically extract values from each
model instance to generate columns. 

As usual, you can use dot-notation to access nested data. However, there is a slight caveat with guessing translation
keys. In this case, only the first section of the name is used for guessing the translation key. For example:

```blade
<x-sleek::entity-table
    :entities="[
        new User(['tenant' => ['name' => 'FBI']]),
    ]"
    :columns="['tenant.name']"
/>
```
Here, the key for translating the column header will only be `users.fields.tenant`, while the value for the row will
correctly resolve to 'FBI'. This logic is more intuitive when dotting into nested data, as usually, you're looking for
a displayable value inside a nested structure that describes this structure. In this specific example, we want to
display the 'tenant' by using it's name, so only using the first section of a dotted name feels more correct.

As usual, if this doesn't work for you, you can explicitly specify an accessor. Since a custom accessor is a common
occurance, there's a semi-shortcut in addition to the explicit syntax:
```blade
<x-sleek::entity-table
    :entities="[
        new User(['tenant' => ['name' => 'FBI']]),
    ]"
    :columns="['tenant' => 'tenant.name', 'also-tenant' => ['accessor' => 'tenant.name']]"
/>
```
In this example, both column definitions are equivalent.

### Custom Columns

Value extraction always needs to resolve to a "stringable" value, but sometimes you need markup in a column - for bades,
buttons, etc. To facilitate this, we patched the blade compiler to support our own flavour of "slot properties":

```blade
<x-sleek::entity-table
    :entities="[
        new User(['name' => 'James Bond']),
    ]"
>
    <x-slot:column-name bind="$name">
        <strong>{{ $name }}</strong>
    </x-slot:column-name>
</x-sleek::entity-table>
```

Let's unpack this: 

Here we have a slot called `column-name` with a special attribute `bind`. We patched the blade
compiler to transform any slot with the attribute `bind` into a "callable slot" by turning the slot into a callback.
This allows us to render the slot multiple times and pass parameters to it, the parameters to the callback are named
via the value to the `bind` property - so in our example, the first parameter to the callback will be called `name`.
You can then use the parameter in your slot as it will be in scope when the slot is rendered.

For each column of each row, `sleek::entity-table` will look for a slot called `column-<name>`, where name is the name
of the column to be rendered. If found, it will then call this callable slot, passing the extracted value of that column
and the full row value as the first and second parameter respectively.

In effect, this allows us to define custom markup for each column or even handle virtual columns where we ignore the
extracted value and render custom html instead. For example, you might define an "actions" column that renders
navigational links for each record:

```blade
<x-sleek::entity-table
    :entitites="[
        new User(['name' => 'James Bond'])
    ]"
    :columns="['name', 'actions']"
>
    <x-sleek:column-actions bind="$_, $user">
        <a href="{{ route('users.show', $user) }}">Show</a>
    </x-sleek:column-actions>
</x-sleek::entity-table>
```

### Styling Columns

In addition to the bind attribute, you can pass any additional attributes to the slot and they will be passed to the
underlying `<td>`-element:

```blade
<x-sleek::entity-table
    :entitites="[
        new User(['name' => 'James Bond'])
    ]"
    :columns="['name']"
>
    <x-sleek:column-name bind="$name" class="bg-primary">
        {{ $name }}
    </x-sleek:column-name>
</x-sleek::entity-table>
```

> [!IMPORTANT]
> Passing attributes is currently limited and cannot be evaluated conditionally based on the current row or column 
> values.

### Styling Rows

If you want to append attributes to the `<tr>`-element, you can do that via the special `row` slot:

```blade
<x-sleek::entity-table
    :entitites="[
        new User(['name' => 'James Bond'])
    ]"
    :columns="['name']"
>
    <x-slot:row class="bg-success" />
</x-sleek::entity-table>
```

> [!IMPORTANT]
> Passing attributes is currently limited and cannot be evaluated conditionally based on the current row value.

> [!NOTE]
> The content of this slot is always ignored and can only be used to pass attributes.
