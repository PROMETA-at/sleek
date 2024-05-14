@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator && ($navigation === true || $navigation === 'top'))
    {{ $entities->withQueryString()->links() }}
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

</table>
@if ($entities instanceof \Illuminate\Contracts\Pagination\Paginator && ($navigation === true || $navigation === 'bottom'))
  {{ $entities->withQueryString()->links() }}
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
