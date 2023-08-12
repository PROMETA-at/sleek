<x-sleek::form :method="$method" :action="$action" {{ $attributes }}>
    @foreach($fields as $fieldData)
        <x-sleek::form-field
            :name="$fieldData['name']"
            :type="$fieldData['type']"
            :label="$fieldData['label']"
            :attributes="$fieldData['attributes']"
        />
    @endforeach

    {{ $slot }}
</x-sleek::form>
