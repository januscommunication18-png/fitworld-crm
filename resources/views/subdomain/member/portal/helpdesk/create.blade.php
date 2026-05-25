@extends('layouts.subdomain')

@section('title', 'New Request — ' . $host->studio_name)

@section('content')
<x-portal-shell :host="$host" :member="$member">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold">New Support Request</h1>
            <p class="text-base-content/60 mt-1">Send a note to the {{ $host->studio_name }} team and we'll get back to you.</p>
        </div>
        <a href="{{ route('member.portal.helpdesk', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost btn-sm gap-1.5">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-error mb-4">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card bg-base-100 max-w-2xl">
        <div class="card-body">
            <form action="{{ route('member.portal.helpdesk.store', ['subdomain' => $host->subdomain]) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="subject" class="label-text font-medium">Subject <span class="text-error">*</span></label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}"
                           class="input input-bordered w-full @error('subject') input-error @enderror"
                           placeholder="e.g., Question about my membership" required maxlength="255">
                </div>
                <div>
                    <label for="member-helpdesk-message" class="label-text font-medium">Message <span class="text-error">*</span></label>
                    <input type="hidden" name="message" id="member-helpdesk-message-hidden" value="{{ old('message') }}">
                    <div id="member-helpdesk-message-editor" class="bg-base-100"></div>
                </div>
                <div class="flex items-center gap-2 pt-2 border-t border-base-200">
                    <button type="submit" id="member-helpdesk-submit" class="btn btn-primary">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Request
                    </button>
                    <a href="{{ route('member.portal.helpdesk', ['subdomain' => $host->subdomain]) }}" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-portal-shell>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}">
<style>
    #member-helpdesk-message-editor { min-height: 160px; }
    #member-helpdesk-message-editor .ql-editor { min-height: 160px; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var editorEl = document.getElementById('member-helpdesk-message-editor');
    var hidden = document.getElementById('member-helpdesk-message-hidden');
    var form = hidden ? hidden.closest('form') : null;
    if (!editorEl || !hidden || !form) return;

    var quill = new Quill(editorEl, {
        theme: 'snow',
        placeholder: 'Tell us what you need help with...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['link', 'blockquote'],
                ['clean']
            ]
        }
    });

    if (hidden.value) quill.root.innerHTML = hidden.value;

    form.addEventListener('submit', function (e) {
        var text = quill.getText().trim();
        if (!text) {
            e.preventDefault();
            editorEl.classList.add('border', 'border-error', 'rounded');
            quill.focus();
            return;
        }
        hidden.value = quill.root.innerHTML;
    });
});
</script>
@endpush
