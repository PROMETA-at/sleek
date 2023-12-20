<div class="d-flex flex-column">
    <div class="d-flex justify-content-between mx-2">
        <div>
        @isset($title)
            <h1>{{$title}}</h1>
        @endisset
        </div>

        <div {{$actions->attributes}}>
        @isset($actions)
            {{$actions}}
        @endisset
        </div>
    </div>

    <hr style="width: 95%; margin: 0 auto;"/>

    <dl class="row mt-2" style="margin-bottom: 0">
    @foreach($fields as $field)
        <dt class="col-md-6 text-md-end">{{$field['label']}}</dt>
        @php
            $columnSlotName = \Illuminate\Support\Str::camel("column-{$field['name']}");
            $value = data_get($model, $field['name']);
        @endphp
        @isset(${$columnSlotName})
            <dd {{${$columnSlotName}->attributes->class(['col-md-6'])}} {{ ${$columnSlotName}->attributes }}>
                {{ ${$columnSlotName}($value, $model) }}
            </dd>
        @else
            <dd class="col-md-6">{{$value}}</dd>
        @endisset
    @endforeach
    </dl>

    @isset($attach)
    <div {{$attach->attributes}}>
        {{$attach}}
    </div>
    @endisset
</div>
