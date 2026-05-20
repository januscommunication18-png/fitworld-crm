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

<div class="flex-shrink-0">
    <details class="js-actions-dropdown" data-align="{{ $align }}" onclick="event.stopPropagation()">
        <summary class="btn btn-ghost btn-{{ $size }} {{ $label ? 'gap-1.5' : 'btn-square' }} list-none cursor-pointer {{ $hoverOnly ? 'opacity-0 group-hover:opacity-100 focus:opacity-100 transition-opacity' : '' }}">
            <span class="{{ $icon }} size-5"></span>
            @if($label)<span>{{ $label }}</span>@endif
        </summary>
        <ul class="js-actions-menu menu menu-sm bg-base-100 rounded-box {{ $width }} p-1.5 shadow-lg border border-base-300 hidden" style="z-index: 9999; position: fixed;">
            {{ $slot }}
        </ul>
    </details>
</div>

@once
@push('scripts')
<script>
(function () {
    function closeAll(except) {
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(function (d) {
            if (d === except) return;
            d.removeAttribute('open');
            var menu = d.querySelector('.js-actions-menu');
            if (menu) menu.classList.add('hidden');
        });
    }

    function positionMenu(details) {
        var summary = details.querySelector('summary');
        var menu = details.querySelector('.js-actions-menu');
        if (!summary || !menu) return;
        var rect = summary.getBoundingClientRect();
        menu.classList.remove('hidden');
        var menuRect = menu.getBoundingClientRect();
        var align = details.dataset.align || 'end';
        var top = rect.bottom + 4;
        // Flip up if it would overflow viewport bottom
        if (top + menuRect.height > window.innerHeight - 8) {
            top = Math.max(8, rect.top - menuRect.height - 4);
        }
        var left = align === 'end' ? (rect.right - menuRect.width) : rect.left;
        // Clamp horizontally
        left = Math.max(8, Math.min(left, window.innerWidth - menuRect.width - 8));
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
    }

    document.addEventListener('toggle', function (e) {
        var d = e.target;
        if (!(d instanceof HTMLDetailsElement) || !d.classList.contains('js-actions-dropdown')) return;
        if (d.open) {
            closeAll(d);
            positionMenu(d);
        } else {
            var menu = d.querySelector('.js-actions-menu');
            if (menu) menu.classList.add('hidden');
        }
    }, true);

    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(function (d) {
            if (!d.contains(e.target)) {
                d.removeAttribute('open');
                var menu = d.querySelector('.js-actions-menu');
                if (menu) menu.classList.add('hidden');
            }
        });
    });

    // Reposition on scroll/resize so open menu stays anchored to the trigger
    function repositionOpen() {
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(positionMenu);
    }
    window.addEventListener('scroll', repositionOpen, true);
    window.addEventListener('resize', repositionOpen);
})();
</script>
@endpush
@endonce
