@php
    if (!isset($cancel)) $cancel = new \Illuminate\View\ComponentSlot();
    if (!isset($submit)) $submit = new \Illuminate\View\ComponentSlot();
    if (!isset($dialog)) $dialog = new \Illuminate\View\ComponentSlot();
    if (!isset($body)) $body = new \Illuminate\View\ComponentSlot();
@endphp
<div class="modal fade" {{ $attributes }}>
    <div {{ $dialog->attributes->class(['modal-dialog', 'modal-dialog-centered'])->style(['height: 90%']) }}>
        <div class="modal-content" style="max-height: 100%; overflow-y: auto">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <x-sleek::form :action="$action" :method="$method">
                <div {{ $body->attributes->class(['modal-body']) }}>
                    {{ $slot ?? $body }}
                </div>
                <div class="modal-footer">
                    <button {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }} type="button" data-bs-dismiss="modal">{{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}</button>
                    <button type="submit" class="btn btn-success" :disabled="loading">
                        <span x-show="loading" class="spinner-border spinner-border-sm modal-spinner" role="status" aria-hidden="true"></span>
                        {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
                    </button>
                </div>
            </x-sleek::form>
        </div>
    </div>
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
            document.querySelectorAll('.modal-spinner').forEach(function(spinner) {
                spinner.classList.add('hidden-spinner');
            });
        } else {
            console.log('Alpine.js is loaded.');
        }
    });
</script>
@endonce
