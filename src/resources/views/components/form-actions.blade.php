@php
    if (!isset($cancel)) $cancel = new \Illuminate\View\ComponentSlot();
    if (!isset($submit)) $submit = new \Illuminate\View\ComponentSlot();
@endphp
<div {{$attributes}}>
    <button {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }} type="button" data-bs-dismiss="modal">{{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}</button>
    <button type="submit" class="btn btn-success" :disabled="loading">
        <span x-show="loading" class="spinner-border spinner-border-sm form-spinner" role="status" aria-hidden="true"></span>
        {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
    </button>
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

