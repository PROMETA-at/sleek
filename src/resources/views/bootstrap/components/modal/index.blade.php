@props([
    'id',
    'backdrop' => 'true',
    'keyboard' => 'true',
    'focus' => 'true',
    'scrollable' => true,
    'centered' => false,
    'fade' => false,
])

@ensureSlotFor($header)
@ensureSlotFor($body)
@ensureSlotFor($footer)


<div class="modal @if($fade) fade @endif"
     id="{{ $id }}"
     tabindex="-1"
     aria-hidden="true"
     data-bs-backdrop="{{ $backdrop }}"
     data-bs-keyboard="{{ $keyboard }}"
     data-bs-focus="{{ $focus }}"
>
    <div class="modal-dialog @if($centered) modal-dialog-centered @endif @if($scrollable) modal-dialog-scrollable @endif">
        <div class="modal-content" {{ $attributes }}>
            @isset($header)
                <div class="modal-header" {{ $header->attributes }}>
                    {{ $header }}
                </div>
            @endisset

            @isset($body)
                <div class="modal-body" {{ $body->attributes }}>
                    {{ $body }}
                </div>
            @endisset

            {{ $slot }}

            @isset($footer)
                <div class="modal-footer" {{ $footer->attributes }}>
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
