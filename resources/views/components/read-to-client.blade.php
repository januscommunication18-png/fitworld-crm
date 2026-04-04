@php
    $rtcHost = $rtcHost ?? (auth()->guard('web')->user()?->currentHost() ?? auth()->guard('web')->user()?->host ?? null);
    // Fallback: check if $host is available in the parent view (subdomain pages)
    if (!$rtcHost && isset($host)) {
        $rtcHost = $host;
    }
    $rtcText = $rtcHost?->payment_settings['read_to_client'] ?? '';
    $rtcHasText = !empty(trim($rtcText));
    $rtcCheckboxId = 'rtc-agreed-' . ($rtcId ?? 'default');
    $rtcSubmitId = $rtcSubmitBtn ?? null;
    // On public pages, show as "Terms & Conditions" instead of "Read to Client"
    $rtcLabel = $rtcPublicMode ?? false ? 'Guide For Client' : 'Read to Client';
    $rtcAgreeText = $rtcPublicMode ?? false
        ? 'I have read and agree to the above'
        : 'Do you understand and agree to the terms as I have explained them?';
@endphp

@if($rtcHasText)
<div class="bg-info/5 border border-info/20 rounded-xl p-4 {{ $rtcClass ?? 'mt-4' }}">
    <div class="flex items-center gap-2 mb-3">
        <span class="icon-[tabler--file-text] size-5 text-info"></span>
        <h4 class="font-semibold text-info text-sm">{{ $rtcLabel }}</h4>
    </div>
    <div class="text-sm text-base-content/80 border border-info/10 rounded-lg p-3 bg-base-100 max-h-48 overflow-y-auto leading-relaxed">
        {!! nl2br(e($rtcText)) !!}
    </div>
    <label class="flex items-start gap-3 cursor-pointer mt-3 p-3 bg-base-100 border border-info/20 rounded-lg hover:bg-info/5 transition-colors">
        <input type="checkbox" id="{{ $rtcCheckboxId }}" name="client_agreed_terms" value="1"
               class="checkbox checkbox-info checkbox-sm mt-0.5 rtc-checkbox"
               data-submit-btn="{{ $rtcSubmitId }}"
               onchange="toggleRtcSubmit(this)">
        <div>
            <span class="font-medium text-sm">{{ ($rtcPublicMode ?? false) ? 'I agree to the terms' : 'Customer agrees to the terms' }}</span>
            <p class="text-xs text-base-content/60">{{ $rtcAgreeText }}</p>
        </div>
    </label>
</div>

@once
@push('scripts')
<script>
function toggleRtcSubmit(checkbox) {
    var btnId = checkbox.dataset.submitBtn;
    if (btnId) {
        var btn = document.getElementById(btnId);
        if (btn) btn.disabled = !checkbox.checked;
    }
}
// Disable submit buttons on page load if read-to-client exists
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.rtc-checkbox').forEach(function(cb) {
        var btnId = cb.dataset.submitBtn;
        if (btnId) {
            var btn = document.getElementById(btnId);
            if (btn) btn.disabled = !cb.checked;
        }
    });
});
</script>
@endpush
@endonce
@endif
