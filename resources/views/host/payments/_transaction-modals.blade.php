@php
    $modalHost = auth()->user()->currentHost() ?? auth()->user()->host ?? null;
    $readToClient = $modalHost?->payment_settings['read_to_client'] ?? '';
    $hasReadToClient = !empty(trim($readToClient));
@endphp

@push('modals')
{{-- Confirm Payment Drawer --}}
<div id="confirm-drawer-backdrop" class="fixed inset-0 bg-black/50 z-50 hidden transition-opacity" onclick="closeConfirmDrawer()"></div>
<div id="confirm-drawer" class="fixed top-0 right-0 h-full w-full max-w-3xl bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 flex flex-col">
    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-base-200 shrink-0">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <span class="icon-[tabler--check-circle] size-6 text-success"></span>
            Confirm Payment
        </h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeConfirmDrawer()">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>

    <form id="confirm-form" method="POST" class="flex-1 flex flex-col overflow-hidden">
        @csrf
        <div class="flex-1 overflow-y-auto">
            <div>
                {{-- Payment Details --}}
                <div class="p-5 space-y-4">

                    <div class="flex items-center justify-between p-3 bg-base-200/50 rounded-lg">
                        <div>
                            <p class="text-sm text-base-content/60">Reference</p>
                            <p id="confirm-transaction-id" class="font-mono font-semibold"></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-base-content/60">Amount</p>
                            <p id="confirm-amount" class="text-xl font-bold text-success"></p>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label" for="confirm-manual-method">
                            <span class="label-text font-medium">Payment Type</span>
                        </label>
                        <select id="confirm-manual-method" name="manual_method" class="select select-bordered w-full" required>
                            @foreach($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cash Payment Fields --}}
                    <div id="cash-payment-fields" class="hidden space-y-3 p-3 bg-base-200/50 rounded-lg">
                        <div class="flex items-center gap-2 text-sm font-medium text-base-content/70">
                            <span class="icon-[tabler--cash-banknote] size-4"></span>
                            Cash Payment
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="form-control">
                                <label class="label py-1" for="received_amount">
                                    <span class="label-text text-sm">Received</span>
                                </label>
                                <label class="input input-bordered input-sm flex items-center gap-1">
                                    <span class="text-base-content/50">$</span>
                                    <input type="number" id="received_amount" name="received_amount" step="0.01" min="0"
                                           class="grow w-full" placeholder="0.00" oninput="updateReturnedAmount()">
                                </label>
                            </div>
                            <div class="form-control">
                                <label class="label py-1" for="returned_amount">
                                    <span class="label-text text-sm">Change</span>
                                </label>
                                <label class="input input-bordered input-sm flex items-center gap-1 bg-base-100">
                                    <span class="text-base-content/50">$</span>
                                    <input type="number" id="returned_amount" name="returned_amount" step="0.01" min="0"
                                           class="grow w-full" placeholder="0.00" readonly>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-control">
                        <label class="label" for="confirm-notes">
                            <span class="label-text font-medium">Notes (optional)</span>
                        </label>
                        <textarea id="confirm-notes"
                                  name="notes"
                                  class="textarea textarea-bordered h-20"
                                  placeholder="Payment notes..."></textarea>
                    </div>
                </div>

                {{-- Read to Client (below payment details) --}}
                @if($hasReadToClient)
                <div class="px-5 pb-5">
                    <div class="bg-info/5 border border-info/20 rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="icon-[tabler--file-text] size-5 text-info"></span>
                            <h4 class="font-semibold text-info">Read to Client</h4>
                        </div>
                        <div class="text-sm text-base-content/80 border border-info/10 rounded-lg p-4 bg-base-100 max-h-60 overflow-y-auto leading-relaxed">
                            {!! nl2br(e($readToClient)) !!}
                        </div>
                        <label class="flex items-start gap-3 cursor-pointer mt-3 p-3 bg-base-100 border border-info/20 rounded-lg hover:bg-info/5 transition-colors">
                            <input type="checkbox" id="terms-agreed" class="checkbox checkbox-info checkbox-sm mt-0.5">
                            <div>
                                <span class="font-medium text-sm">Customer agrees to the terms</span>
                                <p class="text-xs text-base-content/60">Do you understand and agree to the terms as i have explained you?</p>
                            </div>
                        </label>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-2 p-4 border-t border-base-200 shrink-0 bg-base-100">
            <button type="button" class="btn btn-ghost" onclick="closeConfirmDrawer()">Cancel</button>
            <button type="submit" id="confirm-submit-btn" class="btn btn-success gap-1" {{ $hasReadToClient ? 'disabled' : '' }}>
                <span class="icon-[tabler--check] size-4"></span>
                Confirm Payment
            </button>
        </div>
    </form>
</div>

{{-- Cancel Transaction Modal --}}
<div id="cancel-modal" class="fixed inset-0 z-50 hidden" role="dialog" tabindex="-1">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeCancelModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md relative">
            <div class="flex items-center justify-between p-4 border-b border-base-200">
                <h3 class="text-lg font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--x-circle] size-6 text-error"></span>
                    Cancel Transaction
                </h3>
                <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeCancelModal()">
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <form id="cancel-form" method="POST">
                @csrf
                <div class="p-4 space-y-4">
                    <p class="text-base-content/70">
                        Cancel transaction <span id="cancel-transaction-id" class="font-mono font-semibold"></span>?
                    </p>
                    <div class="alert alert-warning">
                        <span class="icon-[tabler--alert-triangle] size-5"></span>
                        <span>This action cannot be undone.</span>
                    </div>
                    <div class="form-control">
                        <label class="label" for="cancel-reason">
                            <span class="label-text">Reason for cancellation <span class="text-error">*</span></span>
                        </label>
                        <textarea id="cancel-reason"
                                  name="reason"
                                  class="textarea textarea-bordered h-24"
                                  placeholder="Enter the reason for cancelling this transaction..."
                                  required></textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-2 p-4 border-t border-base-200">
                    <button type="button" class="btn btn-ghost" onclick="closeCancelModal()">Back</button>
                    <button type="submit" class="btn btn-error gap-1">
                        <span class="icon-[tabler--x] size-4"></span>
                        Cancel Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    var confirmDrawer = document.getElementById('confirm-drawer');
    var confirmBackdrop = document.getElementById('confirm-drawer-backdrop');
    var cancelModal = document.getElementById('cancel-modal');
    var currentConfirmAmount = 0;

    function openConfirmModal(transactionId, transactionCode, amount) {
        document.getElementById('confirm-transaction-id').textContent = transactionCode;
        document.getElementById('confirm-amount').textContent = amount;
        document.getElementById('confirm-form').action = '/payments/transactions/' + transactionId + '/confirm';
        document.getElementById('confirm-notes').value = '';
        document.getElementById('received_amount').value = '';
        document.getElementById('returned_amount').value = '';
        currentConfirmAmount = parseFloat(amount.replace(/[^0-9.]/g, '')) || 0;

        // Reset terms checkbox
        var termsCheckbox = document.getElementById('terms-agreed');
        var submitBtn = document.getElementById('confirm-submit-btn');
        if (termsCheckbox) {
            termsCheckbox.checked = false;
            submitBtn.disabled = true;
        }

        toggleCashFields();

        // Open drawer
        confirmBackdrop.classList.remove('hidden');
        requestAnimationFrame(function() {
            confirmDrawer.classList.remove('translate-x-full');
        });
        document.body.style.overflow = 'hidden';
    }

    function closeConfirmDrawer() {
        confirmDrawer.classList.add('translate-x-full');
        setTimeout(function() {
            confirmBackdrop.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }

    // Keep old name working for existing onclick handlers
    function closeConfirmModal() { closeConfirmDrawer(); }

    function openCancelModal(transactionId, transactionCode) {
        document.getElementById('cancel-transaction-id').textContent = transactionCode;
        document.getElementById('cancel-form').action = '/payments/transactions/' + transactionId + '/cancel';
        document.getElementById('cancel-reason').value = '';
        cancelModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeCancelModal() {
        cancelModal.classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Terms checkbox controls submit button
    var termsCheckbox = document.getElementById('terms-agreed');
    if (termsCheckbox) {
        termsCheckbox.addEventListener('change', function() {
            document.getElementById('confirm-submit-btn').disabled = !this.checked;
        });
    }

    // Toggle cash fields
    var methodSelect = document.getElementById('confirm-manual-method');
    if (methodSelect) {
        methodSelect.addEventListener('change', toggleCashFields);
    }

    function toggleCashFields() {
        var cashFields = document.getElementById('cash-payment-fields');
        var selected = document.getElementById('confirm-manual-method').value;
        cashFields.classList.toggle('hidden', selected !== 'cash');
    }

    function updateReturnedAmount() {
        var received = parseFloat(document.getElementById('received_amount').value) || 0;
        var returned = Math.max(0, received - currentConfirmAmount);
        document.getElementById('returned_amount').value = returned > 0 ? returned.toFixed(2) : '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmDrawer();
            closeCancelModal();
        }
    });
</script>
@endpush
