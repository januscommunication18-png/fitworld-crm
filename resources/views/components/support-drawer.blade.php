{{--
    Support Request Drawer Component

    Usage:
    <x-support-drawer />

    Requires:
    - openSupportDrawer() to open
    - closeSupportDrawer() to close
--}}

@php
    $supportUser = auth()->user();
@endphp

{{-- Drawer Backdrop --}}
<div id="support-drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeSupportDrawer()"></div>

{{-- Support Drawer --}}
<div id="support-drawer" class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
                <span class="icon-[tabler--headset] size-5 text-secondary"></span>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Request Technical Support</h3>
                <p class="text-xs text-base-content/50">Our team will respond within 24 hours</p>
            </div>
        </div>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeSupportDrawer()">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    {{-- Form --}}
    <form id="support-request-form" class="flex flex-col flex-1 overflow-hidden">
        @csrf
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label-text text-sm font-medium" for="support_first_name">First Name <span class="text-error">*</span></label>
                    <input type="text" id="support_first_name" name="first_name" class="input w-full mt-1" value="{{ $supportUser->first_name ?? '' }}" readonly />
                </div>
                <div>
                    <label class="label-text text-sm font-medium" for="support_last_name">Last Name <span class="text-error">*</span></label>
                    <input type="text" id="support_last_name" name="last_name" class="input w-full mt-1" value="{{ $supportUser->last_name ?? '' }}" readonly />
                </div>
            </div>

            <div>
                <label class="label-text text-sm font-medium" for="support_email">Email Address</label>
                <input type="email" id="support_email" name="email" class="input w-full mt-1" value="{{ $supportUser->email ?? '' }}" readonly />
            </div>

            <x-phone-input name="phone" :value="$supportUser->phone ?? ''" label="Phone Number" id-suffix="support-drawer" />

            <div>
                <label class="label-text text-sm font-medium" for="support_note">How can we help? <span class="text-error">*</span></label>
                <textarea id="support_note" name="note" class="textarea textarea-bordered w-full mt-1" rows="4" placeholder="Please describe your issue or question in detail..." required></textarea>
                <p class="text-xs text-base-content/50 mt-1">Please be as detailed as possible so we can assist you better.</p>
            </div>

            <div id="support-error" class="alert alert-error hidden">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span id="support-error-text"></span>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" id="submit-support-btn" class="btn btn-primary">
                <span class="icon-[tabler--send] size-4"></span>
                Submit Request
            </button>
            <button type="button" class="btn btn-ghost" onclick="closeSupportDrawer()">Cancel</button>
        </div>
    </form>
</div>

<script>
function openSupportDrawer() {
    var drawer = document.getElementById('support-drawer');
    var backdrop = document.getElementById('support-drawer-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100');
        drawer.classList.remove('translate-x-full');
    }
}

// Keep old function name as alias for backward compatibility
function openSupportModal() { openSupportDrawer(); }

function closeSupportDrawer() {
    var drawer = document.getElementById('support-drawer');
    var backdrop = document.getElementById('support-drawer-backdrop');
    if (drawer && backdrop) {
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100');
        drawer.classList.add('translate-x-full');
    }
    var form = document.getElementById('support-request-form');
    if (form) form.reset();
    // Restore readonly email value after reset
    var emailInput = document.getElementById('support_email');
    if (emailInput) emailInput.value = '{{ $supportUser->email ?? '' }}';
    var errorDiv = document.getElementById('support-error');
    if (errorDiv) errorDiv.classList.add('hidden');
}

function closeSupportModal() { closeSupportDrawer(); }

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('support-request-form');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            var btn = document.getElementById('submit-support-btn');
            var errorDiv = document.getElementById('support-error');
            var errorText = document.getElementById('support-error-text');

            errorDiv.classList.add('hidden');

            var firstName = document.getElementById('support_first_name').value.trim();
            var lastName = document.getElementById('support_last_name').value.trim();
            var email = document.getElementById('support_email').value.trim();
            var note = document.getElementById('support_note').value.trim();

            // Get phone from component
            var phone = '';
            if (typeof PhoneInput_support_drawer !== 'undefined') {
                phone = PhoneInput_support_drawer.getFullNumber();
            }

            if (!firstName || !lastName || !note) {
                errorText.textContent = 'Please fill in all required fields.';
                errorDiv.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Submitting...';

            try {
                var response = await fetch('{{ route("support.requests.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        first_name: firstName,
                        last_name: lastName,
                        email: email,
                        phone: phone,
                        note: note
                    })
                });

                var data = await response.json();

                if (response.ok && data.success) {
                    closeSupportDrawer();
                    showToast(data.message || 'Support request submitted successfully!', 'success');
                    setTimeout(function() { window.location.reload(); }, 1500);
                } else {
                    errorText.textContent = data.message || 'Failed to submit request. Please try again.';
                    errorDiv.classList.remove('hidden');
                }
            } catch (error) {
                errorText.textContent = 'Network error. Please check your connection and try again.';
                errorDiv.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Submit Request';
            }
        });
    }
});
</script>
