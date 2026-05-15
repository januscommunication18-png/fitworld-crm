{{--
    Actions Dropdown Component

    A three-dot dropdown menu for action items, rendered as an overlay.

    Usage:
    <x-actions-dropdown>
        <li><a href="/edit"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
        <li><button class="text-error"><span class="icon-[tabler--trash] size-4"></span> Delete</button></li>
    </x-actions-dropdown>

    With section dividers:
    <x-actions-dropdown width="w-52">
        <li><a href="/edit"><span class="icon-[tabler--edit] size-4"></span> Edit</a></li>
        <li class="menu-title text-xs uppercase text-base-content/40 pt-2">Status</li>
        <li><button class="text-success">Publish</button></li>
    </x-actions-dropdown>

    Props:
    - width:      Menu width class (default: 'w-48')
    - icon:       Icon class for the trigger button (default: 'icon-[tabler--dots-vertical]')
    - size:       Button size: 'xs', 'sm', 'md' (default: 'sm')
    - hover-only: Show button only on parent hover (default: false) — parent needs 'group' class
    - align:      Dropdown alignment: 'end', 'start' (default: 'end')
    - label:      Optional text label next to the icon (default: null)
--}}

@props([
    'width' => 'w-48',
    'icon' => 'icon-[tabler--dots-vertical]',
    'size' => 'sm',
    'hoverOnly' => false,
    'align' => 'end',
    'label' => null,
])

<div class="relative flex-shrink-0">
    <details class="dropdown dropdown-bottom dropdown-{{ $align }}">
        <summary class="btn btn-ghost btn-{{ $size }} {{ $label ? 'gap-1.5' : 'btn-square' }} list-none cursor-pointer {{ $hoverOnly ? 'opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity' : '' }}">
            <span class="{{ $icon }} size-5"></span>
            @if($label)<span>{{ $label }}</span>@endif
        </summary>
        <ul class="dropdown-content menu menu-sm bg-base-100 rounded-box {{ $width }} p-1.5 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; {{ $align === 'end' ? 'right: 0;' : 'left: 0;' }} top: 100%;">
            {{ $slot }}
        </ul>
    </details>
</div>
