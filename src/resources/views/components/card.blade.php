@props(['reactivity' => false])
<div {{$attributes->class(['card', 'sleek-reactivity' => $reactivity])}} {{$attributes}}>
    @isset($header)
    <div class="card-header">
        {{$header}}
    </div>
    @endisset

    <div class="card-body">
        {{$slot}}
    </div>

    @isset($footer)
        <div class="card-footer">
            {{$footer}}
        </div>
    @endisset
</div>
@once
    @if($reactivity)
        <style>
            .sleek-reactivity {
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            }

            .sleek-reactivity:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.3);
            }
        </style>
    @endif
@endonce
