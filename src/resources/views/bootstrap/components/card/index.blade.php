<div {{ $attributes->class(['card']) }}>
  @isset($headerImage)
    <img {{ $headerImage->attributes->class(['card-img-top']) }}>
  @endisset
  @isset($header)
    @php($attributes = $header->attributes)
    <x-bs::card.header {{ $attributes }}>
      {{ $header }}
    </x-bs::card.header>
  @endisset

  @isset($body)
    @php($attributes = $body->attributes)
    <x-bs::card.body {{ $attributes }}>
      {{ $body }}
    </x-bs::card.body>
  @endisset

  {{ $slot }}

  @isset($footer)
    @php($attributes = $footer->attributes)
    <x-bs::card.footer {{ $attributes }}>
      {{ $footer }}
    </x-bs::card.footer>
  @endisset
</div>
