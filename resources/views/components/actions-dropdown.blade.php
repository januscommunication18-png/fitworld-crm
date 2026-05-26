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
    // Track menu-to-details mapping after teleporting menus to <body>.
    function getMenuFor(details) {
        return details._actionsMenu || details.querySelector('.js-actions-menu');
    }

    function closeAll(except) {
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(function (d) {
            if (d === except) return;
            d.removeAttribute('open');
            var menu = getMenuFor(d);
            if (menu) menu.classList.add('hidden');
        });
    }

    function positionMenu(details) {
        var summary = details.querySelector('summary');
        var menu = getMenuFor(details);
        if (!summary || !menu) return;

        // Teleport the menu to <body> so it escapes any ancestor that creates
        // a containing block for position:fixed (e.g. an element with CSS
        // `transform`, `filter`, or `perspective` — the slide-in drawer uses
        // `transform: translate-x-*` for its animation, which would otherwise
        // make `position: fixed` resolve relative to the drawer and push the
        // menu off-screen).
        if (menu.parentNode !== document.body) {
            details._actionsMenu = menu;
            document.body.appendChild(menu);
        }

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
            var menu = getMenuFor(d);
            if (menu) menu.classList.add('hidden');
        }
    }, true);

    document.addEventListener('click', function (e) {
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(function (d) {
            var menu = getMenuFor(d);
            // The menu was teleported to <body>, so it's no longer inside
            // the <details> element. Treat clicks inside the menu as inside
            // the dropdown, not outside.
            if (d.contains(e.target)) return;
            if (menu && menu.contains(e.target)) return;
            d.removeAttribute('open');
            if (menu) menu.classList.add('hidden');
        });
    });

    // Close the dropdown when any item inside it is clicked (button/link/etc.).
    // Capture-phase + setTimeout(0) so the item's own onclick (e.g. openDrawer) still
    // fires first, but the dropdown collapses regardless of whether the handler
    // calls stopPropagation.
    document.addEventListener('click', function (e) {
        var actionable = e.target.closest('.js-actions-menu a, .js-actions-menu button');
        if (!actionable) return;
        var menu = actionable.closest('.js-actions-menu');
        // Find the <details> this menu belongs to. After teleport the menu is
        // no longer a DOM child of details, so we scan open dropdowns for the
        // one that owns this menu reference.
        var owner = null;
        document.querySelectorAll('details.js-actions-dropdown[open]').forEach(function (d) {
            if (getMenuFor(d) === menu) owner = d;
        });
        if (!owner) return;
        setTimeout(function () {
            owner.removeAttribute('open');
            menu.classList.add('hidden');
        }, 0);
    }, true);

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
