@props([
  'scrollable' => true,
  'centered' => false,
  'size' => null,
])

<div class="
      modal-dialog
      @if($centered) modal-dialog-centered @endif
      @if($scrollable) modal-dialog-scrollable @endif
      @if($size) modal-{{ $size }} @endif
    ">
  <x-bs::modal.content {{ $attributes }}>
    @forwardSlots
  </x-bs::modal.content>
</div>