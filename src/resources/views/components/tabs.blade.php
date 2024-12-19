@props(['key', 'default' => null])

<div>
    <x-bs::tabs.header>
            {{ $header }}
    </x-bs::tabs.header>
    <x-bs::tabs.content :key="$key">
        {{ $slot }}
    </x-bs::tabs.content>
</div>
