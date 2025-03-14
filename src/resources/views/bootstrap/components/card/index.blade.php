@ensureSlotFor($header)
@ensureSlotFor($body)
@ensureSlotFor($footer)

<div {{ $attributes->class(['card']) }}>
  @isset($headerImage)
    <img {{ $headerImage->attributes->class(['card-img-top']) }}>
  @endisset
  @isset($header)
    <x-bs::card.header {{ $header->attributes }}>
      {{ $header }}
    </x-bs::card.header>
  @endisset

  @isset($body)
    <x-bs::card.body {{ $body->attributes }}>
      {{ $body }}
    </x-bs::card.body>
  @endisset

  {{ $slot }}

  @isset($footer)
    <x-bs::card.footer {{ $footer->attributes }}>
      {{ $footer }}
    </x-bs::card.footer>
  @endisset
</div>
