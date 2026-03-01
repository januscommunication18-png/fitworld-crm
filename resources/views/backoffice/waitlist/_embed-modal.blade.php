{{-- Embed Code Modal --}}
<div id="embed-code-modal" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeEmbedModal()"></div>

    {{-- Modal Content --}}
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-lg shadow-xl w-full max-w-2xl pointer-events-auto relative z-10">
            {{-- Header --}}
            <div class="flex items-center justify-between p-4 border-b border-base-content/10">
                <h3 class="text-lg font-semibold">Embed Waitlist Form</h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeEmbedModal()">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <div class="p-6 space-y-6">
                {{-- Direct Link --}}
                <div>
                    <label class="label-text font-medium mb-2 block">Direct Link</label>
                    <p class="text-sm text-base-content/60 mb-2">Share this link directly with your audience.</p>
                    <div class="flex gap-2">
                        <input type="text" id="direct-link" class="input w-full font-mono text-sm"
                            value="{{ route('public.waitlist.form') }}" readonly>
                        <button type="button" class="btn btn-outline btn-square" onclick="copyToClipboard('direct-link')" title="Copy">
                            <span class="icon-[tabler--copy] size-5"></span>
                        </button>
                        <a href="{{ route('public.waitlist.form') }}" target="_blank" class="btn btn-outline btn-square" title="Open">
                            <span class="icon-[tabler--external-link] size-5"></span>
                        </a>
                    </div>
                </div>

                {{-- Iframe Embed --}}
                <div>
                    <label class="label-text font-medium mb-2 block">Iframe Embed</label>
                    <p class="text-sm text-base-content/60 mb-2">Copy this code to embed the form in any website.</p>
                    <div class="relative">
                        <textarea id="iframe-code" class="textarea w-full font-mono text-sm h-24" readonly><iframe src="{{ route('public.waitlist.form') }}" width="100%" height="700" frameborder="0" style="border: none; max-width: 500px;"></iframe></textarea>
                        <button type="button" class="btn btn-sm btn-outline absolute top-2 right-2" onclick="copyToClipboard('iframe-code')">
                            <span class="icon-[tabler--copy] size-4"></span>
                            Copy
                        </button>
                    </div>
                </div>

                {{-- JavaScript Embed --}}
                <div>
                    <label class="label-text font-medium mb-2 block">JavaScript Embed</label>
                    <p class="text-sm text-base-content/60 mb-2">For more control, use this JavaScript snippet.</p>
                    <div class="relative">
                        <textarea id="js-code" class="textarea w-full font-mono text-sm h-32" readonly><div id="fitcrm-waitlist"></div>
<script>
(function() {
    var iframe = document.createElement('iframe');
    iframe.src = '{{ route('public.waitlist.form') }}';
    iframe.width = '100%';
    iframe.height = '700';
    iframe.style.border = 'none';
    iframe.style.maxWidth = '500px';
    document.getElementById('fitcrm-waitlist').appendChild(iframe);
})();
</script></textarea>
                        <button type="button" class="btn btn-sm btn-outline absolute top-2 right-2" onclick="copyToClipboard('js-code')">
                            <span class="icon-[tabler--copy] size-4"></span>
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 p-4 border-t border-base-content/10">
                <button type="button" class="btn btn-ghost" onclick="closeEmbedModal()">Close</button>
            </div>
        </div>
    </div>
</div>
