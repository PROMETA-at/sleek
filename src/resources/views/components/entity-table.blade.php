@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator && ($navigation === true || $navigation === 'top'))
    {{ $entities->withQueryString()->links(data: [
        'pageSizeName' => $pageSizeName,
        'pageName' => $pageName,
    ]) }}
@endif
<x-bs::table striped hover :size="$size" {{ $attributes->class('sleek-entity-table') }}>
    <thead>
        <tr>
            @foreach($columns as $column)
                <th>
                    @if($column['sortable'])
                        <a
                            href="{{ $sortedRoute($column['name']) }}"
                            hx-boost="true"
                        >
                            {{ $column['label'] }}
                            @if(!(request($sortDirectionName) && request($sortByName) === $column['name']))
                                <i class="bi bi-chevron-expand"></i>
                            @elseif(request($sortDirectionName) === 'asc')
                                <i class="bi bi-sort-up"></i>
                            @elseif(request($sortDirectionName) === 'desc')
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
                    <td data-label="{{$column['label'] ?? ''}}" {{ ${$columnSlotName}->attributes }} {{ ${$columnSlotName}->attributes }}>
                        {{ ${$columnSlotName}($value, $entity) }}
                    </td>
                @else
                    <td data-label="{{$column['label'] ?? ''}}">{{ $value }}</td>
                @endisset
            @endforeach
        </tr>
        @endforeach
    </tbody>

</x-bs::table>
@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator && ($navigation === true || $navigation === 'bottom'))
    {{ $entities->withQueryString()->links(data: [
        'pageSizeName' => $pageSizeName,
        'pageName' => $pageName,
    ]) }}
@endif
