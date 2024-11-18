@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator && ($navigation === true || $navigation === 'top'))
    {{ $entities->withQueryString()->links(data: [
        'pageSizeName' => $pageSizeName,
        'pageName' => $pageName,
    ]) }}
@endif
<x-bs::table striped hover :size="$size" {{ $attributes }}>
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
<style>
    @media screen and (max-width: 600px) {
        table {
            border: 0;
        }

        table caption {
            font-size: 1.3em;
        }

        table thead {
            border: none;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }

        table tbody {
            display: block;
        }

        table tr {
            border-bottom: 3px solid #ddd;
            display: block;
            margin-bottom: .625em;
        }

        table td {
            border-bottom: 1px solid #ddd;
            display: block;
            font-size: .8em;
            text-align: right;
        }

        table td::before {
            /*
            * aria-label has no advantage, it won't be read inside a table
            content: attr(aria-label);
            */
            content: attr(data-label);
            float: left;
            font-weight: bold;
            text-transform: uppercase;
        }

        table td:last-child {
            border-bottom: 0;
        }
    }
</style>
