@props([
    'src' => null,
    'initials' => '??',
    'alt' => '',
    'size' => 'md',
    'class' => '',
])

@php
    $sizes = [
        'xs' => 'size-6 text-xs',
        'sm' => 'size-8 text-sm',
        'md' => 'size-10 text-sm',
        'lg' => 'size-12 text-base',
        'xl' => 'size-16 text-lg',
        '2xl' => 'size-20 text-xl',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($src)
    <div class="avatar {{ $class }}">
        <div class="{{ $sizeClass }} rounded-full">
            <img src="{{ $src }}" alt="{{ $alt }}">
        </div>
    </div>
@else
    <div class="avatar placeholder {{ $class }}">
        <div class="{{ $sizeClass }} bg-primary text-primary-content rounded-full">
            <span>{{ $initials }}</span>
        </div>
    </div>
@endif
