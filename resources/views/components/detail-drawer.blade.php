@props([
    'id',
    'title' => 'Details',
    'size' => 'lg', // sm, md, lg, xl, 2xl, 3xl
    'showFooter' => true,
])

@php
$sizeClasses = [
    'sm' => 'max-w-sm',      // 384px
    'md' => 'max-w-md',      // 448px
    'lg' => 'max-w-lg',      // 512px
    'xl' => 'max-w-xl',      // 576px
    '2xl' => 'max-w-2xl',    // 672px
    '3xl' => 'max-w-3xl',    // 768px
    '4xl' => 'max-w-4xl',    // 896px
];
$maxWidth = $sizeClasses[$size] ?? $sizeClasses['lg'];
@endphp

<div
    id="drawer-{{ $id }}"
    class="fixed top-0 right-0 h-full w-full {{ $maxWidth }} bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out hidden flex flex-col"
    role="dialog"
    tabindex="-1"
    data-drawer-id="{{ $id }}"
>
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">{{ $title }}</h3>
        <div class="flex items-center gap-1">
            {{ $headerActions ?? '' }}
            <button type="button" class="btn btn-ghost btn-circle btn-sm" aria-label="Close" onclick="closeDrawer('{{ $id }}')">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto p-4">
        {{ $slot }}
    </div>

    {{-- Footer --}}
    @if($showFooter)
    <div class="p-4 border-t border-base-200 flex justify-between gap-2">
        @if(isset($footer))
            {{ $footer }}
        @else
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('{{ $id }}')">Close</button>
        @endif
    </div>
    @endif
</div>
