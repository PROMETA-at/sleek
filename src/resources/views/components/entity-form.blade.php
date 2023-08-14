<x-sleek::form :method="$method" :action="$action" {{ $attributes }}>
    @foreach($fields as $fieldData)
        @component('Prometa\Sleek\Views\Components\FormField', 'sleek::form-field', $fieldData)
        @endcomponentClass
    @endforeach

    {{ $slot }}
</x-sleek::form>
