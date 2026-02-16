/**
 * Reusable Drawer Component
 *
 * Usage:
 * - Add onclick="openDrawer('unique-id')" to trigger buttons
 * - Use <x-detail-drawer id="unique-id"> component for the drawer
 */

// Open drawer by ID
function openDrawer(drawerId, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const drawer = document.getElementById('drawer-' + drawerId);
    if (!drawer) {
        console.warn('Drawer not found:', drawerId);
        return;
    }

    // Show the drawer
    drawer.classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Add backdrop if not exists
    let backdrop = document.getElementById('drawer-backdrop-' + drawerId);
    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-black/50 z-40 transition-opacity duration-300 opacity-0';
        backdrop.id = 'drawer-backdrop-' + drawerId;
        backdrop.onclick = function() {
            closeDrawer(drawerId);
        };
        document.body.appendChild(backdrop);
    } else {
        backdrop.classList.remove('hidden');
    }

    // Trigger animation after a small delay
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        backdrop.classList.add('opacity-100');
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');
    });

    // Dispatch custom event
    drawer.dispatchEvent(new CustomEvent('drawer:opened', { detail: { drawerId } }));
}

// Close drawer by ID
function closeDrawer(drawerId) {
    const drawer = document.getElementById('drawer-' + drawerId);
    const backdrop = document.getElementById('drawer-backdrop-' + drawerId);

    if (drawer) {
        drawer.classList.remove('translate-x-0');
        drawer.classList.add('translate-x-full');
    }

    if (backdrop) {
        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
    }

    // Wait for animation to complete before hiding
    setTimeout(() => {
        if (drawer) {
            drawer.classList.add('hidden');
            // Dispatch custom event
            drawer.dispatchEvent(new CustomEvent('drawer:closed', { detail: { drawerId } }));
        }
        if (backdrop) {
            backdrop.classList.add('hidden');
        }
        document.body.style.overflow = '';
    }, 300);
}

// Close drawer on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const openDrawers = document.querySelectorAll('[id^="drawer-"]:not(.hidden):not([id*="backdrop"])');
        openDrawers.forEach(drawer => {
            const drawerId = drawer.dataset.drawerId || drawer.id.replace('drawer-', '');
            closeDrawer(drawerId);
        });
    }
});

// Export for module usage if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { openDrawer, closeDrawer };
}
