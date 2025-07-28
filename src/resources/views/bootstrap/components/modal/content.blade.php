@ensureSlotFor($header)
@ensureSlotFor($body)
@ensureSlotFor($footer)

<div {{ $attributes->class(['modal-content']) }}>
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
