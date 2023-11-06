@props(['show' => ['submit']])
@php
    if (!isset($cancel)) $cancel = new \Illuminate\View\ComponentSlot();
    if (!isset($submit)) $submit = new \Illuminate\View\ComponentSlot();
    if (!isset($reset)) $reset = new \Illuminate\View\ComponentSlot();
@endphp
<div {{$attributes}}>
    @if(in_array('reset', $show))
        <button class="btn btn-outline-warning" type="button" x-on:dblclick="$el.closest('form').reset()" title="Double Click to perform">
            {{ $reset->attributes->get('label') ?? __('common.actions.reset') }}
        </button>
    @endif

    @if(in_array('cancel', $show))
        <a href="{{url()->previous()}}" {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }}>
            {{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}
        </a>
    @endif

    @if(in_array('submit', $show))
        <button type="submit" class="btn btn-success" :disabled="loading">
            <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
            {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
        </button>
    @endif
</div>
<style>
    .hidden-spinner{
        display: none;
    }
</style>
@once
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            if (typeof Alpine === 'undefined') {
                console.log('Alpine.js is not loaded.');
                document.querySelectorAll('.form-spinner').forEach(function(spinner) {
                    spinner.classList.add('hidden-spinner');
                });
            } else {
                console.log('Alpine.js is loaded.');
            }
        });
    </script>
@endonce

