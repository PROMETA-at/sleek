# UI Components

## Icons

Ever get tired of typing `<i class="bi bi-envelope"></i>` over and over? Sleek's icon component turns that into
something you can actually read at a glance:

```blade
<x-icon envelope />
```

That's it. One attribute, no quotes, no class strings. The result is a proper `<i class="bi bi-envelope"></i>` element,
ready to go.

### How It Works

The component uses a boolean attribute shorthand: the first truthy attribute it encounters becomes the icon name.
So `<x-icon person-fill />` renders `<i class="bi bi-person-fill"></i>` -- the attribute name *is* the icon name.

If you prefer to be explicit (or need to use a dynamic value), the `name` attribute works too:

```blade
<x-icon name="envelope" />
<x-icon :name="$iconName" />
```

### Additional Attributes

Any extra attributes you pass are forwarded straight to the underlying `<i>` element. Classes are merged intelligently
with the `bi bi-{name}` base classes, so you can style freely without worrying about clobbering anything:

```blade
<x-icon envelope class="text-primary" style="font-size: 1.5rem;" />
{{-- Renders: <i class="bi bi-envelope text-primary" style="font-size: 1.5rem;"></i> --}}
```

## Alerts

Imagine you just saved a user in your controller and want to flash a success message. With Sleek, that's one line:

```php
use Prometa\Sleek\Facades\Sleek;

Sleek::raise('User created successfully', 'success');
```

Back in the browser, a polished toast notification slides in with an appropriate icon, an animated progress bar counting
down 4 seconds, and then gracefully dismisses itself. Hover over it and the timer pauses -- because nobody likes
a message that vanishes while you're reading it.

If you're using the standard Sleek layout (`sleek::view`), the alert component is already wired up. There's nothing
else to configure. Just `raise()` from your controller and you're done.

### Alert Types

The second parameter controls the alert style. Each type automatically picks a matching Bootstrap icon, so you
never have to think about which icon goes with which message:

```php
Sleek::raise('Welcome back!', 'primary');
Sleek::raise('FYI: maintenance tonight', 'info');
Sleek::raise('User created successfully', 'success');
Sleek::raise('Something went wrong', 'danger');
Sleek::raise('Careful with that setting', 'warning');
```

All supported types: `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `light`, `dark`.

### Positioning

By default, alerts appear in a sensible spot. If you want them somewhere specific, configure the position once
in your `AppServiceProvider`:

```php
Sleek::alert([
    'position' => 'top-right'
]);
```

Available positions: `center`, `top-left`, `top-right`, `bottom`, `bottom-left`, `bottom-right`.

### Manual Placement

If you're not using the Sleek layout, just drop the component into your own layout and the alert system works
exactly the same way:

```blade
<x-sleek::alert />
```

## Cards

Sleek wraps Bootstrap's card component so you can focus on content instead of markup ceremony:

```blade
<x-sleek::card>
    Here's your dashboard summary.
</x-sleek::card>
```

The default slot becomes the card body. Clean and simple.

### Slots

Need a header or footer? Use named slots. Attributes on each slot are preserved and forwarded to the
corresponding card element:

```blade
<x-sleek::card>
    <x-slot:header class="bg-primary text-white">User Profile</x-slot:header>

    Profile details go here.

    <x-slot:footer class="text-muted">Last updated: today</x-slot:footer>
</x-sleek::card>
```

You can use any combination -- header only, footer only, both, or neither. The card adapts.

### Reactivity

For cards that represent clickable items or interactive elements, the `reactivity` prop adds a subtle hover
shadow transition that gives the user a visual cue:

```blade
<x-sleek::card :reactivity="true">
    Hover over me and watch the shadow appear.
</x-sleek::card>
```

This is especially nice for dashboard tiles or card grids where you want to signal interactivity without
overdoing it.
