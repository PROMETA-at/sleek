# Eloquent Extensions

## Auto Sort
As described in the [Sorting Controls](tables.md#sorting-controls) section, sorting can require repetitive boilerplate. The **auto sort helper** automates this process based on request parameters. 

When `autoSort()` is called, it checks for the following request parameters: 
- `sort-by`: The column name to sort by.
- `sort-direction`: The sort direction (`asc` or `desc`, defaults to `desc`). 

If these parameters are present, `autoSort()` automatically applies the necessary `orderBy` clause. 
```php
$request->all();
/* [
 *     'sort-by' => 'name',
 *     'sort-direction' => 'asc'
 * ]
 */

User::query()
    // Will apply ->orderBy('name', 'asc')
    ->autoSort()
    ->get()
```

## Auto Paginate
Building on the behavior described in the [Pagination Controls](tables.md#pagination-controls), the **auto paginate helper** automatically applies pagination parameters from the request to your query. When you call `autoPaginate()`, it looks for the following parameters:
- `page-size`: The number of items per page (defaults to a given value if it's not provided in the request)
- `page`: The page that needs to be rendered

If a request includes a `page-size` parameter, as shown below:

```php
$request->all();
/* [
 *     'page-size' => '100',
 *     'page' => '1'
 * ]
 */
```
Calling `autoPaginate()` like this:

```php
User::query()
    ->autoPaginate(50) // Default items per page 50
    // Will append ->paginate(100)
```

Automatically adjusts the query to display 100 items per page by appending `->paginate(100)`. This ensures that the `page-size` parameter from the request takes precedence over the default value.

If the request does not include a `page-size` parameter, `autoPaginate()` will use the default size specified in the method call, such as 50 in this case.

## Auto Filter
The **auto filter helper** simplifies the process of applying query filters based on request parameters. When `autoFilter()` is called, it dynamically checks the request for filter parameters and applies them to the query using a configured filter pipeline.

#### Filter Pipeline
There are several built-in filters for the most common use cases:
- `equals`: Filters by exact match
- `like`: Filters by partial match using LIKE
- `contains`: Filters JSON fields to check if they contain a specific value
- `gt`: Filters by greater than
- `lt`: Filters by less than
- `gte`: Filters by greater than or equal to
- `lte`: Filters by less than or equal to
- `for_each`: Processes each value in a comma-sperated list with a given operator from the list above  (e.g. `for_each|equals`)
- `boolean`: This filter ensures that a value is properly cast to a boolean before applying a comparison operator (e.g., `boolean|equals`). It is especially helpful when working with values that might otherwise be compared as strings, such as `'true'` or `'false'`.

#### Example Usage 
To use the filtering functionality, you need to create a form on your page with the corresponding input fields. For the example provided, the form should contain three fields: `name`, `age` and `role`. These fields allow users to define their filter criteria, which will be processed by `autoFilter()`. The `autoFilter()` helper automatically retrieves and applies the parameters from the request to filter the query.

If the request includes the following parameters: 
```php
/* [
 *     'name' => 'James', 
 *     'age' => 25, 
 *     'role' => 'admin,user' 
 * ]
 */
```

You can configure `autoFilter()` for example as follows:

```php
User::query()
    ->autoFilter([
        'name' => 'like', 
        // ->where('name', 'like', '%James%')
        'age' => 'gte', 
        // `->where('age', '>=', 25)`
        'role' => 'for_each|equals' 
        // ->where('role', 'admin')->where('role', 'user')
    ])
    ->get()
```

This configuration tells `autoFilter()` to dynamically append the suitable conditions to the query based on the request parameters:

- `name`: appends `->where('name', 'like', '%James%')`
- `age`: appends `->where('age', '>=', 25)`
- `role`: appends `->where('role', 'admin')->where('role', 'user')`
