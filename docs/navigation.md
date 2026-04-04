# Navigation

## Breadcrumbs

Sleek provides an automatic breadcrumb component that generates navigation breadcrumbs based on your current route structure. Simply add the component to your layout and it will intelligently create breadcrumbs from the route segments:

```blade
<x-sleek::breadcrumbs />
```

The breadcrumbs component automatically:
- Generates breadcrumbs from the current route structure
- Uses translation keys following the pattern `breadcrumbs.<segment>` for each URL segment
- Handles route parameters intelligently, especially when they represent models

For example, if your current route is `/users/123/edit`, the component will look for translations:
- `breadcrumbs.users` for the "users" segment
- `breadcrumbs.edit` for the "edit" segment
- For the "123" segment, it will try to resolve it as a model (see Custom Breadcrumb Labels below)

## Custom Breadcrumb Labels

When your routes include model parameters (like `/users/{user}/edit`), Sleek can automatically display meaningful labels instead of just the model ID. To enable this, implement the `RendersAsBreadcrumb` interface on your models:

```php
use Prometa\Sleek\RendersAsBreadcrumb;

class User extends Model implements RendersAsBreadcrumb
{
    public function asBreadcrumb(): string
    {
        return $this->name;
    }
}
```

Now when the breadcrumbs component encounters a User model in the route parameters, it will display the user's name instead of their ID. This makes your breadcrumbs much more user-friendly:

- Without interface: `Home > Users > 123 > Edit`
- With interface: `Home > Users > John Doe > Edit`
