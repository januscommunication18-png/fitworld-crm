{{-- Global Alert Modal - Reusable across the application --}}
<div id="global-alert-modal" class="fixed inset-0 z-[9999] hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="window.FitCRM.closeAlertModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-md w-full p-6 pointer-events-auto relative transform transition-all">
            {{-- Close Button --}}
            <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3" onclick="window.FitCRM.closeAlertModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>

            {{-- Icon --}}
            <div id="global-alert-icon" class="w-14 h-14 rounded-full bg-warning/20 flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--alert-triangle] size-7 text-warning"></span>
            </div>

            {{-- Title --}}
            <h3 id="global-alert-title" class="font-bold text-lg text-center mb-2">Alert</h3>

            {{-- Message --}}
            <p id="global-alert-message" class="text-base-content/70 text-center mb-6">Something requires your attention.</p>

            {{-- Actions --}}
            <div id="global-alert-actions" class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" class="btn btn-ghost" onclick="window.FitCRM.closeAlertModal()">Cancel</button>
                <a id="global-alert-action-btn" href="#" class="btn btn-primary">
                    <span id="global-alert-action-icon" class="icon-[tabler--plus] size-4"></span>
                    <span id="global-alert-action-text">Take Action</span>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
window.FitCRM = window.FitCRM || {};

/**
 * Show the global alert modal
 * @param {Object} options - Modal configuration
 * @param {string} options.title - Modal title
 * @param {string} options.message - Modal message
 * @param {string} options.icon - Icon class (tabler icon name without prefix)
 * @param {string} options.iconBg - Icon background class (e.g., 'bg-warning/20')
 * @param {string} options.iconColor - Icon color class (e.g., 'text-warning')
 * @param {string} options.actionText - Action button text
 * @param {string} options.actionUrl - Action button URL
 * @param {string} options.actionIcon - Action button icon (tabler icon name without prefix)
 * @param {string} options.actionClass - Action button class (e.g., 'btn-primary')
 * @param {boolean} options.showCancel - Whether to show cancel button (default: true)
 */
window.FitCRM.showAlertModal = function(options) {
    const modal = document.getElementById('global-alert-modal');
    const iconContainer = document.getElementById('global-alert-icon');
    const title = document.getElementById('global-alert-title');
    const message = document.getElementById('global-alert-message');
    const actionBtn = document.getElementById('global-alert-action-btn');
    const actionIcon = document.getElementById('global-alert-action-icon');
    const actionText = document.getElementById('global-alert-action-text');
    const actionsContainer = document.getElementById('global-alert-actions');

    // Set title and message
    title.textContent = options.title || 'Alert';
    message.textContent = options.message || 'Something requires your attention.';

    // Set icon
    if (options.icon) {
        iconContainer.innerHTML = `<span class="icon-[tabler--${options.icon}] size-7 ${options.iconColor || 'text-warning'}"></span>`;
    }
    if (options.iconBg) {
        iconContainer.className = `w-14 h-14 rounded-full ${options.iconBg} flex items-center justify-center mx-auto mb-4`;
    }

    // Set action button
    if (options.actionText) {
        actionText.textContent = options.actionText;
    }
    if (options.actionUrl) {
        actionBtn.href = options.actionUrl;
    }
    if (options.actionIcon) {
        actionIcon.className = `icon-[tabler--${options.actionIcon}] size-4`;
    }
    if (options.actionClass) {
        actionBtn.className = `btn ${options.actionClass}`;
    }

    // Show/hide cancel button
    const cancelBtn = actionsContainer.querySelector('.btn-ghost');
    if (cancelBtn) {
        cancelBtn.style.display = options.showCancel === false ? 'none' : '';
    }

    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

/**
 * Close the global alert modal
 */
window.FitCRM.closeAlertModal = function() {
    const modal = document.getElementById('global-alert-modal');
    modal.classList.add('hidden');
    document.body.style.overflow = '';
};

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('global-alert-modal');
        if (modal && !modal.classList.contains('hidden')) {
            window.FitCRM.closeAlertModal();
        }
    }
});
</script>
