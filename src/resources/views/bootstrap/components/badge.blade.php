@props(['pill' => false])
<span {{ $attributes->class(['badge', 'rounded-pill' => $pill]) }}>{{ $slot }}</span>
