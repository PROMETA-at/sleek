@php
    if (!isset($cancel)) $cancel = new \Illuminate\View\ComponentSlot();
    if (!isset($submit)) $submit = new \Illuminate\View\ComponentSlot();
    if (!isset($dialog)) $dialog = new \Illuminate\View\ComponentSlot();
    if (!isset($body)) $body = new \Illuminate\View\ComponentSlot();
@endphp
<div class="modal fade" id="{{$id}}" {{ $attributes }}>
    <div {{ $dialog->attributes->class(['modal-dialog'])->style(['height: 90%']) }}>
        <div class="modal-content" style="max-height: 100%; overflow-y: auto">
            <div class="modal-header">
                <h5 class="modal-title">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <x-dynamic-component :id="$formId" component="sleek::{{$formType}}" :action="$action" :method="$method" :model="$model" :fields="$fields" :enctype="$enctype">
                    {{ $slot ?? $body }}
                </x-dynamic-component>
            </div>
            @if (!isset($footer))
                <div class="modal-footer">
                    <button {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }} type="button" data-bs-dismiss="modal">{{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}</button>
                    <button type="submit" class="btn btn-success" :disabled="loading" form="{{ $formId }}">
                        <span x-show="loading" class="spinner-border spinner-border-sm modal-spinner hidden-spinner" role="status" aria-hidden="true"></span>
                        {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
                    </button>
                </div>
            @else
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
@once
    <style>
        .hidden-spinner{
            display: none;
        }
    </style>
    <script>
        document.addEventListener('alpine:init', () => {
            console.log('modal-component: alpine.js loaded');
            document.querySelectorAll('.modal-spinner').forEach(function(spinner) {
                spinner.classList.remove('hidden-spinner');
            });
        });
    </script>
@endonce
