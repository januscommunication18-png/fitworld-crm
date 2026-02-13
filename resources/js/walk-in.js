/**
 * Walk-In Booking Modal JavaScript
 * Handles the entire walk-in booking flow
 */

// State management
const walkInState = {
    currentStep: 1,
    sessionType: null, // 'class' or 'service'
    sessionId: null,
    sessionData: null,
    selectedClient: null,
    paymentMethods: null,
    selectedPaymentMethod: null,
    intakeRequired: false,
    intakeQuestionnaireId: null,
};

// API base URL
const apiBase = '/api/v1';

// ==========================================
// Modal Functions
// ==========================================

function openWalkInModal(sessionType, sessionId, sessionData = {}) {
    // Reset state
    resetWalkInState();

    // Set session info
    walkInState.sessionType = sessionType;
    walkInState.sessionId = sessionId;
    walkInState.sessionData = sessionData;

    // Update hidden form
    document.getElementById('walk-in-session-type').value = sessionType;
    document.getElementById('walk-in-session-id').value = sessionId;

    // Update session display
    updateSessionDisplay(sessionData);

    // Show modal - direct DOM manipulation
    const modal = document.getElementById('walk-in-modal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Trigger reflow then add open class for animation
        modal.offsetHeight;
        modal.classList.add('open', 'opened', 'overlay-open');
    }

    // Load recent clients
    loadRecentClients();

    // Check availability if class
    if (sessionType === 'class') {
        checkClassAvailability(sessionId);
    }
}

function closeWalkInModal() {
    const modal = document.getElementById('walk-in-modal');
    if (modal) {
        modal.classList.remove('open', 'opened', 'overlay-open');
        document.body.style.overflow = '';
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    resetWalkInState();
}

function resetWalkInState() {
    walkInState.currentStep = 1;
    walkInState.selectedClient = null;
    walkInState.paymentMethods = null;
    walkInState.selectedPaymentMethod = null;

    // Reset form
    document.getElementById('walk-in-form').reset();

    // Reset UI
    goToStep(1);
    clearSelectedClient();
    document.getElementById('client-search').value = '';
    document.getElementById('client-search-results').classList.add('hidden');
    document.getElementById('client-quick-add-form').classList.add('hidden');
    document.getElementById('client-quick-add-toggle').classList.remove('hidden');

    // Reset next button
    updateNextButton();
}

// ==========================================
// Step Navigation
// ==========================================

function goToStep(step) {
    walkInState.currentStep = step;

    // Hide all steps
    document.querySelectorAll('.walk-in-step-content').forEach(el => el.classList.add('hidden'));

    // Show current step
    const stepContent = document.getElementById(`walk-in-step-${step}`);
    if (stepContent) stepContent.classList.remove('hidden');

    // Update step indicators
    document.querySelectorAll('.walk-in-step').forEach(el => {
        const stepNum = parseInt(el.dataset.step);
        const indicator = el.querySelector('.step-indicator');

        if (stepNum < step) {
            el.classList.remove('opacity-50');
            indicator.classList.remove('bg-base-300');
            indicator.classList.add('bg-success', 'text-success-content');
            indicator.innerHTML = '<span class="icon-[tabler--check] size-4"></span>';
        } else if (stepNum === step) {
            el.classList.remove('opacity-50');
            indicator.classList.remove('bg-base-300', 'bg-success', 'text-success-content');
            indicator.classList.add('bg-primary', 'text-primary-content');
            indicator.textContent = stepNum;
        } else {
            el.classList.add('opacity-50');
            indicator.classList.remove('bg-primary', 'text-primary-content', 'bg-success', 'text-success-content');
            indicator.classList.add('bg-base-300');
            indicator.textContent = stepNum;
        }
    });

    // Update back button
    const backBtn = document.getElementById('walk-in-back-btn');
    if (step > 1) {
        backBtn.classList.remove('hidden');
    } else {
        backBtn.classList.add('hidden');
    }

    // Update next button
    updateNextButton();
}

function walkInNextStep() {
    if (walkInState.currentStep === 1) {
        // Validate client selected
        if (!walkInState.selectedClient) {
            showToast('Please select a client', 'warning');
            return;
        }
        // Load payment methods and go to step 2
        loadPaymentMethods();
        goToStep(2);
    } else if (walkInState.currentStep === 2) {
        // Validate payment method
        if (!walkInState.selectedPaymentMethod) {
            showToast('Please select a payment method', 'warning');
            return;
        }
        // Update confirmation and go to step 3
        updateConfirmation();
        goToStep(3);
    } else if (walkInState.currentStep === 3) {
        // Submit booking
        submitWalkInBooking();
    }
}

function walkInPrevStep() {
    if (walkInState.currentStep > 1) {
        goToStep(walkInState.currentStep - 1);
    }
}

function updateNextButton() {
    const nextBtn = document.getElementById('walk-in-next-btn');
    const step = walkInState.currentStep;

    if (step === 1) {
        nextBtn.disabled = !walkInState.selectedClient;
        nextBtn.innerHTML = 'Continue <span class="icon-[tabler--arrow-right] size-4"></span>';
    } else if (step === 2) {
        nextBtn.disabled = !walkInState.selectedPaymentMethod;
        nextBtn.innerHTML = 'Continue <span class="icon-[tabler--arrow-right] size-4"></span>';
    } else if (step === 3) {
        nextBtn.disabled = false;
        nextBtn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Confirm Booking';
    }
}

// ==========================================
// Session Display
// ==========================================

function updateSessionDisplay(data) {
    document.getElementById('session-name').textContent = data.name || 'Loading...';

    // Handle datetime display
    const datetimeEl = document.getElementById('session-datetime');
    if (data.datetime) {
        datetimeEl.textContent = data.datetime;
        // Store for confirmation
        const parts = data.datetime.split(' ');
        walkInState.sessionData.date = parts.slice(0, 3).join(' ');
        walkInState.sessionData.time = parts.slice(3).join(' ');
    } else if (data.date && data.time) {
        datetimeEl.textContent = `${data.date} at ${data.time}`;
    } else {
        datetimeEl.textContent = '---';
    }

    // Handle capacity/duration display
    const spotsEl = document.getElementById('session-spots');
    if (data.capacity !== undefined && data.bookedCount !== undefined) {
        const remaining = data.capacity - data.bookedCount;
        spotsEl.textContent = `${remaining} spots left`;
        spotsEl.className = remaining > 0 ? 'ml-2 badge badge-sm badge-success' : 'ml-2 badge badge-sm badge-error';
    } else if (data.duration !== undefined) {
        spotsEl.textContent = `${data.duration} min`;
        spotsEl.className = 'ml-2 badge badge-sm badge-ghost';
    } else {
        spotsEl.textContent = '';
    }

    // Update icon based on type
    const iconEl = document.getElementById('session-type-icon');
    if (walkInState.sessionType === 'service') {
        iconEl.className = 'icon-[tabler--massage] size-6';
    } else {
        iconEl.className = 'icon-[tabler--calendar-event] size-6';
    }
}

async function checkClassAvailability(sessionId) {
    try {
        const response = await fetch(`${apiBase}/walk-in/class/${sessionId}/availability`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await response.json();

        if (data.availability) {
            const spotsEl = document.getElementById('session-spots');
            spotsEl.textContent = `${data.availability.remaining} spots left`;
            spotsEl.className = data.availability.available ? 'badge badge-sm badge-success' : 'badge badge-sm badge-error';

            // Store availability for capacity override logic
            walkInState.sessionData.availability = data.availability;
        }
    } catch (error) {
        console.error('Error checking availability:', error);
    }
}

// ==========================================
// Client Functions
// ==========================================

let searchTimeout = null;

function searchClients(query) {
    clearTimeout(searchTimeout);

    if (query.length < 2) {
        document.getElementById('client-search-results').classList.add('hidden');
        document.getElementById('client-recent-section').classList.remove('hidden');
        return;
    }

    document.getElementById('client-search-loading').classList.remove('hidden');

    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`${apiBase}/clients/search?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await response.json();

            displayClientResults(data.clients);
        } catch (error) {
            console.error('Error searching clients:', error);
        } finally {
            document.getElementById('client-search-loading').classList.add('hidden');
        }
    }, 300);
}

function displayClientResults(clients) {
    const resultsEl = document.getElementById('client-results-list');
    const resultsSection = document.getElementById('client-search-results');
    const recentSection = document.getElementById('client-recent-section');

    if (clients.length === 0) {
        resultsEl.innerHTML = `
            <div class="text-center py-4 text-base-content/50">
                <span class="icon-[tabler--user-off] size-8 mb-2"></span>
                <p class="text-sm">No clients found</p>
            </div>
        `;
    } else {
        resultsEl.innerHTML = clients.map(client => createClientCard(client)).join('');
    }

    resultsSection.classList.remove('hidden');
    recentSection.classList.add('hidden');
}

async function loadRecentClients() {
    try {
        const response = await fetch(`${apiBase}/clients/recent`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        const data = await response.json();

        const listEl = document.getElementById('client-recent-list');
        if (data.clients.length === 0) {
            listEl.innerHTML = `
                <div class="text-center py-4 text-base-content/50">
                    <p class="text-sm">No recent clients</p>
                </div>
            `;
        } else {
            listEl.innerHTML = data.clients.map(client => createClientCard(client)).join('');
        }
    } catch (error) {
        console.error('Error loading recent clients:', error);
        document.getElementById('client-recent-list').innerHTML = `
            <div class="text-center py-4 text-error">
                <p class="text-sm">Error loading clients</p>
            </div>
        `;
    }
}

function createClientCard(client) {
    const initials = (client.first_name[0] + client.last_name[0]).toUpperCase();
    const contact = client.email || client.phone || 'No contact info';
    const memberBadge = client.is_member ? '<span class="badge badge-secondary badge-xs">Member</span>' : '';

    return `
        <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg hover:bg-base-200/50 cursor-pointer transition-colors"
             onclick='selectClient(${JSON.stringify(client)})'>
            <div class="flex items-center gap-3">
                <div class="avatar placeholder">
                    <div class="bg-base-300 text-base-content rounded-full w-10">
                        <span class="text-sm font-medium">${initials}</span>
                    </div>
                </div>
                <div>
                    <div class="font-medium">${client.full_name} ${memberBadge}</div>
                    <div class="text-sm text-base-content/60">${contact}</div>
                </div>
            </div>
            <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
        </div>
    `;
}

function selectClient(client) {
    walkInState.selectedClient = client;
    document.getElementById('walk-in-client-id').value = client.id;

    // Update selected client display
    const displayEl = document.getElementById('selected-client-display');
    const initials = (client.first_name[0] + client.last_name[0]).toUpperCase();

    document.getElementById('selected-client-initials').textContent = initials;
    document.getElementById('selected-client-name').textContent = client.full_name;
    document.getElementById('selected-client-contact').textContent = client.email || client.phone || 'No contact info';

    // Hide search/recent, show selected
    document.getElementById('client-search').closest('.form-control').classList.add('hidden');
    document.getElementById('client-search-results').classList.add('hidden');
    document.getElementById('client-recent-section').classList.add('hidden');
    document.getElementById('client-quick-add-section').classList.add('hidden');
    displayEl.classList.remove('hidden');

    // Update next button
    updateNextButton();
}

function clearSelectedClient() {
    walkInState.selectedClient = null;
    document.getElementById('walk-in-client-id').value = '';

    // Show search/recent, hide selected
    document.getElementById('client-search').closest('.form-control').classList.remove('hidden');
    document.getElementById('client-recent-section').classList.remove('hidden');
    document.getElementById('client-quick-add-section').classList.remove('hidden');
    document.getElementById('selected-client-display').classList.add('hidden');

    updateNextButton();
}

function toggleQuickAddForm() {
    const form = document.getElementById('client-quick-add-form');
    const toggle = document.getElementById('client-quick-add-toggle');

    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        toggle.classList.add('hidden');
        document.getElementById('quick-add-first-name').focus();
    } else {
        form.classList.add('hidden');
        toggle.classList.remove('hidden');
    }
}

async function createQuickAddClient() {
    const firstName = document.getElementById('quick-add-first-name').value.trim();
    const lastName = document.getElementById('quick-add-last-name').value.trim();
    const phone = document.getElementById('quick-add-phone').value.trim();
    const email = document.getElementById('quick-add-email').value.trim();
    const sendEmails = document.getElementById('quick-add-send-emails').checked;

    if (!firstName || !lastName) {
        showToast('Please enter first and last name', 'warning');
        return;
    }

    try {
        const response = await fetch(`${apiBase}/clients/quick-add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                first_name: firstName,
                last_name: lastName,
                phone: phone,
                email: email,
                send_emails: sendEmails
            })
        });

        const data = await response.json();

        if (data.success) {
            selectClient(data.client);
            showToast(data.existing ? 'Existing client selected' : 'Client created successfully', 'success');
        } else {
            showToast(data.message || 'Error creating client', 'error');
        }
    } catch (error) {
        console.error('Error creating client:', error);
        showToast('Error creating client', 'error');
    }
}

// ==========================================
// Payment Functions
// ==========================================

async function loadPaymentMethods() {
    document.getElementById('payment-methods-loading').classList.remove('hidden');
    document.getElementById('payment-methods-list').classList.add('hidden');

    try {
        const classPlanId = walkInState.sessionData.class_plan_id || '';
        const response = await fetch(`${apiBase}/walk-in/payment-methods/${walkInState.selectedClient.id}?class_plan_id=${classPlanId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        const data = await response.json();
        walkInState.paymentMethods = data.payment_methods;

        displayPaymentMethods(data.payment_methods);
    } catch (error) {
        console.error('Error loading payment methods:', error);
        showToast('Error loading payment methods', 'error');
    } finally {
        document.getElementById('payment-methods-loading').classList.add('hidden');
    }
}

function displayPaymentMethods(methods) {
    document.getElementById('payment-methods-list').classList.remove('hidden');

    // Membership option
    if (methods.membership?.available) {
        const membershipEl = document.getElementById('payment-option-membership');
        membershipEl.classList.remove('hidden');
        document.getElementById('membership-details').textContent =
            `${methods.membership.label} - ${methods.membership.credits_remaining} credits`;
    }

    // Pack option
    if (methods.pack?.available) {
        const packEl = document.getElementById('payment-option-pack');
        packEl.classList.remove('hidden');

        const packOptionsEl = document.getElementById('pack-options');
        packOptionsEl.innerHTML = methods.pack.packs.map(pack => `
            <label class="flex items-center gap-2 text-sm">
                <input type="radio" name="selected_pack" value="${pack.id}" class="radio radio-xs" onchange="selectPack(${pack.id})">
                <span>${pack.name}</span>
                <span class="badge badge-ghost badge-xs">${pack.classes_remaining} left</span>
                ${pack.expires_at ? `<span class="text-xs text-base-content/50">exp. ${pack.expires_at}</span>` : ''}
            </label>
        `).join('');
    }

    // Stripe option
    if (methods.stripe?.available) {
        document.getElementById('payment-option-stripe').classList.remove('hidden');
    }

    // Comp option
    if (methods.comp?.available) {
        document.getElementById('payment-option-comp').classList.remove('hidden');
    }

    // Set default price for manual payment
    if (walkInState.sessionData.price) {
        document.getElementById('manual-payment-amount').value = walkInState.sessionData.price;
    }
}

function selectPaymentMethod(method) {
    walkInState.selectedPaymentMethod = method;
    document.getElementById('walk-in-payment-method').value = method;

    // Hide all detail sections
    document.getElementById('manual-payment-details').classList.add('hidden');
    document.getElementById('comp-payment-details').classList.add('hidden');

    // Show relevant details
    if (method === 'manual') {
        document.getElementById('manual-payment-details').classList.remove('hidden');
        document.getElementById('walk-in-manual-method').value = document.getElementById('manual-payment-type').value;
    } else if (method === 'comp') {
        document.getElementById('comp-payment-details').classList.remove('hidden');
    }

    updateNextButton();
}

function selectPack(packId) {
    document.getElementById('walk-in-pack-id').value = packId;
}

function updateManualMethod(method) {
    document.getElementById('walk-in-manual-method').value = method;
}

function updatePricePaid(amount) {
    document.getElementById('walk-in-price-paid').value = amount;
}

// ==========================================
// Intake Functions
// ==========================================

function selectIntakeOption(option) {
    if (option === 'waive') {
        document.getElementById('intake-waive-reason-section').classList.remove('hidden');
        document.getElementById('walk-in-intake-waived').value = '1';
        document.getElementById('walk-in-intake-status').value = 'waived';
    } else {
        document.getElementById('intake-waive-reason-section').classList.add('hidden');
        document.getElementById('walk-in-intake-waived').value = '0';
        document.getElementById('walk-in-intake-status').value = option === 'mark_complete' ? 'completed' : 'pending';
    }
}

function updateIntakeWaiverReason(reason) {
    document.getElementById('walk-in-intake-waived-reason').value = reason;
}

// ==========================================
// Confirmation Functions
// ==========================================

function updateConfirmation() {
    const client = walkInState.selectedClient;
    const initials = (client.first_name[0] + client.last_name[0]).toUpperCase();

    document.getElementById('confirm-client-initials').textContent = initials;
    document.getElementById('confirm-client-name').textContent = client.full_name;
    document.getElementById('confirm-session-name').textContent = walkInState.sessionData.name;
    document.getElementById('confirm-session-datetime').textContent =
        `${walkInState.sessionData.date} at ${walkInState.sessionData.time}`;

    // Payment info
    const paymentMethodLabels = {
        membership: 'Membership Credits',
        pack: 'Class Pack',
        manual: document.getElementById('manual-payment-type').options[document.getElementById('manual-payment-type').selectedIndex].text,
        stripe: 'Card Payment',
        comp: 'Complimentary'
    };

    document.getElementById('confirm-payment-method').textContent =
        paymentMethodLabels[walkInState.selectedPaymentMethod] || walkInState.selectedPaymentMethod;

    const amount = document.getElementById('walk-in-price-paid').value;
    document.getElementById('confirm-payment-amount').textContent =
        walkInState.selectedPaymentMethod === 'membership' || walkInState.selectedPaymentMethod === 'pack'
            ? '1 credit'
            : (amount ? `$${parseFloat(amount).toFixed(2)}` : 'Free');

    // Warnings
    const warningsEl = document.getElementById('confirm-warnings');
    const capacityWarning = document.getElementById('confirm-capacity-warning');
    const intakeWarning = document.getElementById('confirm-intake-warning');

    let hasWarnings = false;

    // Check capacity override
    if (walkInState.sessionData.availability && !walkInState.sessionData.availability.available) {
        capacityWarning.classList.remove('hidden');
        hasWarnings = true;
    } else {
        capacityWarning.classList.add('hidden');
    }

    // Check intake waived
    if (document.getElementById('walk-in-intake-waived').value === '1') {
        intakeWarning.classList.remove('hidden');
        document.getElementById('confirm-intake-waive-reason').textContent =
            document.getElementById('walk-in-intake-waived-reason').value || 'No reason provided';
        hasWarnings = true;
    } else {
        intakeWarning.classList.add('hidden');
    }

    warningsEl.classList.toggle('hidden', !hasWarnings);
}

function toggleCheckInNow(checked) {
    document.getElementById('walk-in-check-in-now').value = checked ? '1' : '0';
}

// ==========================================
// Submit Booking
// ==========================================

async function submitWalkInBooking() {
    const nextBtn = document.getElementById('walk-in-next-btn');
    nextBtn.disabled = true;
    nextBtn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Processing...';

    // Build payload
    const formData = new FormData(document.getElementById('walk-in-form'));
    const payload = Object.fromEntries(formData);

    // Add payment notes if manual
    if (payload.payment_method === 'manual') {
        payload.payment_notes = document.getElementById('manual-payment-notes').value;
    } else if (payload.payment_method === 'comp') {
        payload.payment_notes = document.getElementById('comp-payment-notes').value;
    }

    // Determine endpoint
    const endpoint = walkInState.sessionType === 'class'
        ? `${apiBase}/walk-in/class/${walkInState.sessionId}`
        : `${apiBase}/walk-in/service/${walkInState.sessionId}`;

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success) {
            showSuccess(data.booking);
        } else {
            showBookingError(data.message);
        }
    } catch (error) {
        console.error('Error submitting booking:', error);
        showBookingError('An error occurred while processing the booking');
    } finally {
        nextBtn.disabled = false;
        updateNextButton();
    }
}

function showSuccess(booking) {
    // Hide step content, show success
    document.querySelectorAll('.walk-in-step-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('walk-in-success').classList.remove('hidden');

    // Hide footer buttons
    document.getElementById('walk-in-footer').classList.add('hidden');

    // Update success display
    const initials = (walkInState.selectedClient.first_name[0] + walkInState.selectedClient.last_name[0]).toUpperCase();
    document.getElementById('success-client-initials').textContent = initials;
    document.getElementById('success-client-name').textContent = walkInState.selectedClient.full_name;
    document.getElementById('success-session-info').textContent =
        `${walkInState.sessionData.name} - ${walkInState.sessionData.time}`;

    const paymentInfo = booking.payment_method === 'membership' || booking.payment_method === 'pack'
        ? `${booking.payment_method} credit`
        : `${booking.payment_method}`;
    document.getElementById('success-payment-info').textContent = paymentInfo;

    if (booking.checked_in) {
        document.getElementById('success-checked-in').classList.remove('hidden');
    }

    showToast('Booking confirmed!', 'success');
}

function showBookingError(message) {
    document.getElementById('confirm-error').classList.remove('hidden');
    document.getElementById('confirm-error-message').textContent = message;
}

function startNewWalkIn() {
    // Reset and stay on modal
    resetWalkInState();
    document.getElementById('walk-in-footer').classList.remove('hidden');

    // Keep session info and reload
    if (walkInState.sessionType && walkInState.sessionId) {
        openWalkInModal(walkInState.sessionType, walkInState.sessionId, walkInState.sessionData);
    }
}

// ==========================================
// Utility Functions
// ==========================================

function showToast(message, type = 'info') {
    // Simple toast notification (can be replaced with your toast library)
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 alert alert-${type} shadow-lg z-50 animate-fade-in`;
    toast.innerHTML = `<span>${message}</span>`;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Export for global access
window.openWalkInModal = openWalkInModal;
window.closeWalkInModal = closeWalkInModal;
window.walkInNextStep = walkInNextStep;
window.walkInPrevStep = walkInPrevStep;
window.searchClients = searchClients;
window.selectClient = selectClient;
window.clearSelectedClient = clearSelectedClient;
window.toggleQuickAddForm = toggleQuickAddForm;
window.createQuickAddClient = createQuickAddClient;
window.selectPaymentMethod = selectPaymentMethod;
window.selectPack = selectPack;
window.updateManualMethod = updateManualMethod;
window.updatePricePaid = updatePricePaid;
window.selectIntakeOption = selectIntakeOption;
window.updateIntakeWaiverReason = updateIntakeWaiverReason;
window.toggleCheckInNow = toggleCheckInNow;
window.startNewWalkIn = startNewWalkIn;
