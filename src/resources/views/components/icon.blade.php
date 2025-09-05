@props(['name' => null])
@php
    // Determine the icon name. Prefer explicit prop if provided, otherwise
    // find the first boolean-like attribute (Blade boolean prop) and use its key as icon name.
    $iconName = $name;

    if (!$iconName) {
        foreach ($attributes->getAttributes() as $key => $value) {
            // Boolean-ish attributes (true or 'true' or null) indicate the chosen icon name
            if ($value === true || $value === 'true' || $value === null) {
                $iconName = $key;
                $attributes = $attributes->except($key);
                break;
            }
        }
    }

    if ($iconName) $attributes = $attributes->merge(['class' => "bi bi-{$iconName}"]);
@endphp

<i {{ $attributes }}></i>
