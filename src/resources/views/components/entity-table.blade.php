@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator)
    {{ $entities->appends([
        'sort-by' => request('sort-by'),
        'sort-direction' => request('sort-direction'),
        'pageSize' => request('pageSize')
    ])->links() }}
@endif

<table {{ $attributes->class(['table', 'table-striped', 'table-hover', "table-$size" => !!$size]) }}>
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>
                    @if($column['sortable'])
                        <a
                            href="{{ $currentRoute([
                                'sort-by' => $column['name'],
                                'sort-direction' => (!request('sort-direction') || request('sort-by') !== $column['name'] || request('sort-direction') === 'desc') ? 'asc' : 'desc'
                            ]) }}"
                            hx-boost="true"
                        >
                            {{ $column['label'] }}
                            @if(!(request('sort-direction') && request('sort-by') === $column['name']))
                                <i class="bi bi-chevron-expand"></i>
                            @elseif(request('sort-direction') === 'asc')
                                <i class="bi bi-sort-up"></i>
                            @elseif(request('sort-direction') === 'desc')
                                <i class="bi bi-sort-down"></i>
                            @endif
                        </a>
                    @else
                        {{ $column['label'] }}
                    @endif

                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($entities as $entity)
        <tr @isset($row){{ $row->attributes }}@endisset>
            @foreach($columns as $column)
                @php($value = data_get($entity, $column['accessor']))
                @php($columnSlotName = \Illuminate\Support\Str::camel("column-{$column['name']}"))
                @isset(${$columnSlotName})
                    <td {{ ${$columnSlotName}->attributes }}>
                        {{ ${$columnSlotName}($value, $entity) }}
                    </td>
                @else
                    <td>{{ $value }}</td>
                @endisset
            @endforeach
        </tr>
        @endforeach
    </tbody>

</table>
