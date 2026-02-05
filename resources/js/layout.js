import 'flyonui/flyonui.js';

/**
 * FitCRM Layout — Sidebar toggle, submenu, mobile drawer
 */
window.FitCRM = window.FitCRM || {};

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('main-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const mobileToggle = document.getElementById('mobile-sidebar-toggle');

    // Desktop: collapse/expand sidebar
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }

    // Mobile: open sidebar drawer
    if (mobileToggle) {
        mobileToggle.addEventListener('click', () => {
            sidebar.classList.add('mobile-open');
            sidebar.classList.remove('collapsed');
            if (backdrop) backdrop.classList.remove('hidden');
        });
    }

    // Close mobile sidebar
    window.FitCRM.closeMobileSidebar = () => {
        if (sidebar) sidebar.classList.remove('mobile-open');
        if (backdrop) backdrop.classList.add('hidden');
    };

    // Submenu toggle
    window.FitCRM.toggleSubmenu = (btn) => {
        const li = btn.closest('li');
        if (!li) return;

        const submenu = li.querySelector('.sidebar-submenu');
        if (!submenu) return;

        // If sidebar is collapsed on desktop, ignore toggle
        if (sidebar && sidebar.classList.contains('collapsed')) return;

        submenu.classList.toggle('open');
    };

    // Auto-open active submenu's parent chevron rotation
    document.querySelectorAll('.nav-item.active .sidebar-submenu').forEach(sub => {
        sub.classList.add('open');
    });
});
