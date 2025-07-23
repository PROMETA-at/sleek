@props([
    'id',
    'backdrop' => 'true',
    'keyboard' => 'true',
    'focus' => 'true',
    'scrollable' => true,
    'centered' => false,
    'fade' => false,
    'showOnLoad' => false,
    'size' => null,
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
     _="
        init
        @if($showOnLoad)
            {{--
                To make sure the above block opens the modal without fade-in animation, we remove the `fade` class
                before showing the modal, then ad it back.
             --}}
            make a bootstrap.Modal from me
            set hadFade to I match .fade
            remove .fade from me
            call it.show() then settle
            if hadFade add .fade to me
        @endif
     "
>
    <div class="
      modal-dialog 
      @if($centered) modal-dialog-centered @endif 
      @if($scrollable) modal-dialog-scrollable @endif
      @if($size) modal-{{ $size }} @endif
    ">
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
