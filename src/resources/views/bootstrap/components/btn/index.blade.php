@props([
  // HTML element: 'button' or 'a'
  'tag' => 'button',
  // Visual variant: primary, secondary, success, danger, warning, info, light, dark, link
  'variant' => 'primary',
  // Outline style
  'outline' => false,
  // Size: sm, md (default), lg
  'size' => 'md',
  // Full width
  'block' => false,
  // Active state class
  'active' => false,
  // Disabled state
  'disabled' => false,
  // For <a> tags
  'href' => null,
  'target' => null,
  // Button type when tag=button
  'type' => 'button',
])

@php
  $isLink = strtolower($tag) === 'a';
  $btnClass = $outline ? 'btn-outline-' . $variant : 'btn-' . $variant;
  $sizeClass = $size === 'lg' ? 'btn-lg' : ($size === 'sm' ? 'btn-sm' : null);
  $classes = [
    'btn',
    $btnClass,
    $sizeClass,
    'w-100' => (bool) $block,
    'active' => (bool) $active,
    'disabled' => $isLink && $disabled,
  ];
  // Attribute bag prepared with classes
  $attributes = $attributes->class($classes);
  // Disabled handling
  if ($isLink) {
    // For <a>, apply role and aria-disabled; prevent click via tabindex
    $attributes = $attributes->merge([
      'role' => 'button',
      'aria-disabled' => $disabled ? 'true' : null,
      'tabindex' => $disabled ? '-1' : null,
    ]);
  }
@endphp

@if ($isLink)
  <a {{ $attributes }} href="{{ $href ?? '#' }}" @if($target) target="{{ $target }}" @endif>
    {{ $slot }}
  </a>
@else
  <button {{ $attributes }} type="{{ $type }}" @if($disabled) disabled @endif>
    {{ $slot }}
  </button>
@endif
