@props([
    'type' => 'primary',
    'size' => 'font-size-12',
    'class' => '',
])

<span class="badge badge-soft-{{ $type }} {{ $size }} {{ $class }}">
    {{ $slot }}
</span>
