{{--
    Global Confirmation Modal

    Usage:
    showConfirmModal({
        title: 'Delete Item',
        message: 'Are you sure?',
        type: 'danger', // danger, warning, success, info
        btnText: 'Delete',
        btnIcon: 'icon-[tabler--trash]',
        onConfirm: function() { ... }
    });
--}}

<!-- Confirmation Modal -->
<div id="confirmModal" class="hidden fixed inset-0 z-[9999]">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal()"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <div class="flex flex-col items-center text-center">
                    <div id="confirmModal_iconWrapper" class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mb-4">
                        <span id="confirmModal_icon" class="icon-[tabler--alert-circle] shrink-0 size-8 text-primary"></span>
                    </div>
                    <h3 class="font-bold text-lg mb-2" id="confirmModal_title">Confirm Action</h3>
                    <p class="text-base-content/70 text-sm" id="confirmModal_message">Are you sure you want to proceed?</p>
                </div>
                <div class="flex justify-center gap-3 mt-6">
                    <button type="button" onclick="closeConfirmModal()" class="btn btn-ghost min-w-24">Cancel</button>
                    <button type="button" id="confirmModal_confirmBtn" class="btn btn-primary min-w-24 gap-2">
                        <span id="confirmModal_btnIcon" class="icon-[tabler--check] shrink-0 size-4"></span>
                        <span id="confirmModal_btnText">Confirm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Modal (for actions that need a form submission) -->
<div id="confirmFormModal" class="hidden fixed inset-0 z-[9999]">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmFormModal()"></div>

    <!-- Modal -->
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <div class="flex flex-col items-center text-center">
                    <div id="confirmFormModal_iconWrapper" class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-4">
                        <span id="confirmFormModal_icon" class="icon-[tabler--trash] shrink-0 size-8 text-error"></span>
                    </div>
                    <h3 class="font-bold text-lg mb-2" id="confirmFormModal_title">Confirm Action</h3>
                    <p class="text-base-content/70 text-sm" id="confirmFormModal_message">Are you sure you want to proceed?</p>
                </div>
                <div class="flex justify-center gap-3 mt-6">
                    <button type="button" onclick="closeConfirmFormModal()" class="btn btn-ghost min-w-24">Cancel</button>
                    <form id="confirmFormModal_form" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="_method" id="confirmFormModal_method" value="POST">
                        <button type="submit" id="confirmFormModal_confirmBtn" class="btn btn-error min-w-24 gap-2">
                            <span id="confirmFormModal_btnIcon" class="icon-[tabler--trash] shrink-0 size-4"></span>
                            <span id="confirmFormModal_btnText">Confirm</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Configuration for different modal types
const modalTypeConfig = {
    danger: {
        icon: 'icon-[tabler--trash]',
        wrapperClass: 'bg-error/10',
        iconClass: 'text-error',
        btnClass: 'btn-error'
    },
    warning: {
        icon: 'icon-[tabler--alert-triangle]',
        wrapperClass: 'bg-warning/10',
        iconClass: 'text-warning',
        btnClass: 'btn-warning'
    },
    success: {
        icon: 'icon-[tabler--check]',
        wrapperClass: 'bg-success/10',
        iconClass: 'text-success',
        btnClass: 'btn-success'
    },
    info: {
        icon: 'icon-[tabler--info-circle]',
        wrapperClass: 'bg-info/10',
        iconClass: 'text-info',
        btnClass: 'btn-info'
    }
};

let confirmModalCallback = null;

// Show confirmation modal with callback
function showConfirmModal(options = {}) {
    const modal = document.getElementById('confirmModal');
    if (!modal) return;

    const config = modalTypeConfig[options.type] || modalTypeConfig.info;

    // Set title and message
    document.getElementById('confirmModal_title').textContent = options.title || 'Confirm Action';
    document.getElementById('confirmModal_message').textContent = options.message || 'Are you sure you want to proceed?';

    // Set icon and styling
    const iconWrapper = document.getElementById('confirmModal_iconWrapper');
    const iconEl = document.getElementById('confirmModal_icon');
    const confirmBtn = document.getElementById('confirmModal_confirmBtn');
    const btnIcon = document.getElementById('confirmModal_btnIcon');
    const btnText = document.getElementById('confirmModal_btnText');

    iconWrapper.className = 'w-16 h-16 rounded-full flex items-center justify-center mb-4 ' + config.wrapperClass;
    iconEl.className = 'shrink-0 size-8 ' + (options.icon || config.icon) + ' ' + config.iconClass;
    confirmBtn.className = 'btn min-w-24 gap-2 ' + config.btnClass;
    btnIcon.className = 'shrink-0 size-4 ' + (options.btnIcon || config.icon);
    btnText.textContent = options.btnText || 'Confirm';

    // Store callback
    confirmModalCallback = options.onConfirm || null;

    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        confirmModalCallback = null;
    }
}

// Handle confirm button click
document.getElementById('confirmModal_confirmBtn')?.addEventListener('click', function() {
    if (confirmModalCallback) {
        confirmModalCallback();
    }
    closeConfirmModal();
});

// Show form-based confirmation modal (for delete, etc.)
function showConfirmFormModal(options = {}) {
    const modal = document.getElementById('confirmFormModal');
    if (!modal) return;

    const config = modalTypeConfig[options.type] || modalTypeConfig.danger;

    // Set title and message
    document.getElementById('confirmFormModal_title').textContent = options.title || 'Confirm Action';
    document.getElementById('confirmFormModal_message').textContent = options.message || 'Are you sure you want to proceed?';

    // Set form action and method
    document.getElementById('confirmFormModal_form').action = options.action || '';
    document.getElementById('confirmFormModal_method').value = options.method || 'POST';

    // Set icon and styling
    const iconWrapper = document.getElementById('confirmFormModal_iconWrapper');
    const iconEl = document.getElementById('confirmFormModal_icon');
    const confirmBtn = document.getElementById('confirmFormModal_confirmBtn');
    const btnIcon = document.getElementById('confirmFormModal_btnIcon');
    const btnText = document.getElementById('confirmFormModal_btnText');

    iconWrapper.className = 'w-16 h-16 rounded-full flex items-center justify-center mb-4 ' + config.wrapperClass;
    iconEl.className = 'shrink-0 size-8 ' + (options.icon || config.icon) + ' ' + config.iconClass;
    confirmBtn.className = 'btn min-w-24 gap-2 ' + config.btnClass;
    btnIcon.className = 'shrink-0 size-4 ' + (options.btnIcon || config.icon);
    btnText.textContent = options.btnText || 'Confirm';

    // Show modal
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeConfirmFormModal() {
    const modal = document.getElementById('confirmFormModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeConfirmModal();
        closeConfirmFormModal();
    }
});

// Helper functions for common actions
const ConfirmModals = {
    // Delete confirmation (form submission)
    delete: function(options) {
        showConfirmFormModal({
            title: options.title || 'Delete',
            message: options.message || 'Are you sure you want to delete this item? This action cannot be undone.',
            type: 'danger',
            icon: 'icon-[tabler--trash]',
            btnIcon: 'icon-[tabler--trash]',
            btnText: options.btnText || 'Delete',
            action: options.action,
            method: 'DELETE'
        });
    },

    // Suspend/Deactivate confirmation (form submission)
    suspend: function(options) {
        showConfirmFormModal({
            title: options.title || 'Suspend',
            message: options.message || 'Are you sure you want to suspend this user?',
            type: 'warning',
            icon: 'icon-[tabler--ban]',
            btnIcon: 'icon-[tabler--ban]',
            btnText: options.btnText || 'Suspend',
            action: options.action,
            method: 'POST'
        });
    },

    // Deactivate confirmation (form submission)
    deactivate: function(options) {
        showConfirmFormModal({
            title: options.title || 'Deactivate',
            message: options.message || 'Are you sure you want to deactivate this user?',
            type: 'warning',
            icon: 'icon-[tabler--user-off]',
            btnIcon: 'icon-[tabler--user-off]',
            btnText: options.btnText || 'Deactivate',
            action: options.action,
            method: 'POST'
        });
    },

    // Activate confirmation (form submission)
    activate: function(options) {
        showConfirmFormModal({
            title: options.title || 'Activate',
            message: options.message || 'Are you sure you want to activate this user?',
            type: 'success',
            icon: 'icon-[tabler--user-check]',
            btnIcon: 'icon-[tabler--user-check]',
            btnText: options.btnText || 'Activate',
            action: options.action,
            method: 'POST'
        });
    },

    // Reset password (AJAX)
    resetPassword: function(options) {
        showConfirmModal({
            title: options.title || 'Reset Password',
            message: options.message || 'Send a password reset email to ' + (options.email || 'this user') + '?',
            type: 'info',
            icon: 'icon-[tabler--key]',
            btnIcon: 'icon-[tabler--send]',
            btnText: options.btnText || 'Send Reset Email',
            onConfirm: function() {
                fetch(options.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    if (ok && data.success !== false) {
                        showToast(data.message || 'Password reset email sent.', 'success');
                    } else {
                        showToast(data.message || 'Failed to send password reset email.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
            }
        });
    },

    // Remove from team (form submission)
    remove: function(options) {
        showConfirmFormModal({
            title: options.title || 'Remove',
            message: options.message || 'Are you sure you want to remove this team member?',
            type: 'danger',
            icon: 'icon-[tabler--trash]',
            btnIcon: 'icon-[tabler--trash]',
            btnText: options.btnText || 'Remove',
            action: options.action,
            method: 'DELETE'
        });
    },

    // Make inactive (form submission)
    inactive: function(options) {
        showConfirmFormModal({
            title: options.title || 'Make Inactive',
            message: options.message || 'Are you sure you want to make this item inactive?',
            type: 'warning',
            icon: 'icon-[tabler--user-off]',
            btnIcon: 'icon-[tabler--user-off]',
            btnText: options.btnText || 'Make Inactive',
            action: options.action,
            method: 'POST'
        });
    }
};

// Toast notification helper
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const bgClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-error' : type === 'warning' ? 'alert-warning' : 'alert-info';
    const iconClass = type === 'success' ? 'icon-[tabler--check]' : type === 'error' ? 'icon-[tabler--x]' : type === 'warning' ? 'icon-[tabler--alert-triangle]' : 'icon-[tabler--info-circle]';

    toast.className = `alert ${bgClass} fixed top-4 right-4 z-[10000] max-w-sm shadow-lg transition-all duration-300`;
    toast.style.transform = 'translateX(100%)';
    toast.style.opacity = '0';
    toast.innerHTML = `<span class="${iconClass} size-5"></span><span>${message}</span>`;
    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    // Slide out after delay
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>
