@props([
    'id',
    'backdrop' => 'true',
    'keyboard' => 'true',
    'focus' => 'true',
    'fade' => false,
    'showOnLoad' => false,
])

<div class="modal @if($fade) fade @endif"
     @isset($id)
        id="{{ $id }}"
     @endisset
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
    <x-bs::modal.dialog {{ $attributes }}>
      @forwardSlots
    </x-bs::modal.dialog>
</div>
