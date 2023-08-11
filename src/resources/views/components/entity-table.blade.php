@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator)
    {{ $entities->links() }}
@endif

<table {{ $attributes->class(['table', 'table-striped', 'table-hover', "table-$size" => !!$size]) }}>
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>
                    {{ $column['label'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($entities as $entity)
        <tr @isset($row){{ $row->attributes }}@endisset>
            @foreach($columns as $column)
                @php($value = $entity->{$column['accessor']})
                @php($columnSlotName = \Illuminate\Support\Str::camel("column-{$column['name']}"))
                @php($columnSlotAttributesName = \Illuminate\Support\Str::camel("column-{$column['name']}-attributes"))
                <td @isset(${$columnSlotAttributesName}){{ ${$columnSlotAttributesName}->attributes }}@endisset>
                    @isset(${$columnSlotName})
                        {{ ${$columnSlotName}($value, $entity) }}
                    @else
                        {{ $value }}
                    @endisset
                </td>
            @endforeach
        </tr>
        @endforeach
    </tbody>

</table>
