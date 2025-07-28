@flags(['close', 'native' => false])

@ensureSlotFor($header)
@ensureSlotFor($extra, true)

@flag('native')
  <dialog {{ $attributes->class([
      'bs',
      'fade',
      'size-xl' => $attributes->get('size') === 'xl',
      'size-lg' => $attributes->get('size') === 'lg',
      'size-sm' => $attributes->get('size') === 'sm',
    ]) }} closedby="any"
  >
    <x-bs::modal.content>
      @forwardSlots

      @isset($header)
        @slot('header', null, $header->attributes->getAttributes())
        {{ $header }}
        @flag('close') <x-bs::btn.close tabindex="-1" onclick="event.target.closest('dialog')?.close()" /> @endflag
        @endslot
      @endisset

      @unless($slot->isEmpty())
        <x-slot:body>
          {{ $slot }}
          @unless(isset($header))
            @flag('close') <x-bs::btn.close tabindex="-1" onclick="event.target.closest('dialog')?.close()"  class="float-end" /> @endflag
          @endunless
        </x-slot:body>
      @endunless

      @isset($extra) {{ $extra }} @endisset
    </x-bs::modal.content>
  </dialog>
@else
  <x-bs::modal {{ $attributes->merge(['fade' => 'true', 'centered' => true]) }}>
      @forwardSlots

      @isset($header)
          @slot('header', null, $header->attributes->getAttributes())
              {{ $header }}
              @flag('close') <x-bs::btn.close modal /> @endflag
          @endslot
      @endisset

      @unless($slot->isEmpty())
          <x-slot:body>
              {{ $slot }}
              @unless(isset($header))
                  @flag('close') <x-bs::btn.close modal class="float-end" /> @endflag
              @endunless
          </x-slot:body>
      @endunless

      @slot('slot', $extra)
  </x-bs::modal>
@endif
