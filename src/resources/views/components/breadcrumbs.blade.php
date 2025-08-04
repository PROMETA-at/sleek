@php
    $pathParts = explode('/', request()->path());
    $parameters = request()->route()->parameters;
    $routeParts = explode('/', request()->route()->uri);

    $resolved = collect($routeParts)
        ->map(function ($label, $idx) use ($parameters, $pathParts) {
            if (preg_match('/{(.*)}/', $label, $matches)) {
                $parameterValue = $parameters[$matches[1]];

                if ($parameterValue instanceof \Prometa\Sleek\RendersAsBreadcrumb) {
                    $label = $parameterValue->asBreadCrumb();
                } else if ($parameterValue instanceof \Illuminate\Database\Eloquent\Model) {
                    $label = $parameterValue->getKey();
                } else if (is_string($parameterValue) || is_numeric($parameterValue)) {
                    $label = $parameterValue;
                }
            } else {
                $label = __('breadcrumbs.'.$label);
            }

            $href = '/'.implode('/', array_slice($pathParts, 0, $idx + 1));
            return compact('label', 'href');
        });
@endphp

<nav {{ $attributes }}>
    <ul class="breadcrumbs">
        @foreach($resolved as $part)
            <li><a href="{{ $part['href'] }}">{{ $part['label'] }}</a></li>
        @endforeach
    </ul>
</nav>
