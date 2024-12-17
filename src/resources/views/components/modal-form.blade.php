@ensureSlotFor($cancel, true)
@ensureSlotFor($submit, true)
@ensureSlotFor($body, true)

@php($formAttributes = ['action', 'method', 'fields', 'enctype', 'target'])

<x-sleek::modal id="{{$id}}" {{ $attributes->except($formAttributes) }}>
    <x-slot:header>
        @isset($header)
            {{ $header }}
        @else
            <h5 class="modal-title">{{ $title }}</h5>
        @endisset
    </x-slot:header>

    <x-slot:extra>
        <x-dynamic-component :id="$formId" component="sleek::{{$formType}}" :model="$model" {{ $attributes->only($formAttributes) }} style="display: contents">
            <x-bs::modal.body>
                {{ $slot ?? $body }}
            </x-bs::modal.body>

            <x-bs::modal.footer>
                @isset($footer)
                    {{ $footer }}
                @else
                    <button {{ $cancel->attributes->except(['label'])->class(['btn', 'btn-outline-secondary']) }} type="button" data-bs-dismiss="modal">{{ $cancel->attributes->get('label') ?? __('common.actions.cancel') }}</button>
                    <button type="submit" class="btn btn-success" :disabled="loading" form="{{ $formId }}">
                        <span x-show="loading" class="spinner-border spinner-border-sm modal-spinner hidden-spinner" role="status" aria-hidden="true"></span>
                        {{ $submit->attributes->get('label') ?? __('common.actions.submit') }}
                    </button>
                @endisset
            </x-bs::modal.footer>
        </x-dynamic-component>
    </x-slot:extra>

</x-sleek::modal>

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
