@extends('layouts.settings')

@section('title', $templateConfig['name'] . ' — Email Templates')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}" />
<style>
#email-editor {
    font-size: 0.875rem;
}
#email-editor .ql-toolbar {
    border-radius: 0.5rem 0.5rem 0 0;
    border-color: hsl(var(--bc) / 0.2);
    background: hsl(var(--b2));
}
#email-editor .ql-container {
    border-radius: 0 0 0.5rem 0.5rem;
    border-color: hsl(var(--bc) / 0.2);
    min-height: 300px;
}
#email-editor .ql-editor {
    min-height: 280px;
}
.variable-chip {
    cursor: pointer;
    transition: all 0.15s;
}
.variable-chip:hover {
    transform: translateY(-1px);
}
</style>
@endpush

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.communication.email-templates') }}">Email Templates</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $templateConfig['name'] }}</li>
    </ol>
@endsection

@section('settings-content')
<form id="templateForm" action="{{ route('settings.communication.email-templates.update', $key) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold">{{ $templateConfig['name'] }}</h2>
                <p class="text-sm text-base-content/60 mt-1">{{ $templateConfig['description'] }}</p>
            </div>
            <a href="{{ route('settings.communication.email-templates') }}" class="btn btn-ghost btn-sm">
                <span class="icon-[tabler--arrow-left] size-4"></span>
                Back
            </a>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="alert alert-success">
                <span class="icon-[tabler--check] size-5"></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col xl:flex-row gap-6">
            {{-- Main Editor Column --}}
            <div class="flex-1 min-w-0 space-y-4">
                {{-- Subject Line --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <label class="label" for="subject">
                            <span class="label-text font-medium">Subject Line</span>
                        </label>
                        <input type="text"
                               id="subject"
                               name="subject"
                               class="input input-bordered w-full @error('subject') input-error @enderror"
                               value="{{ old('subject', $template?->subject ?? $defaultSubject) }}"
                               placeholder="Enter email subject..."
                               required />
                        <p class="text-xs text-base-content/60 mt-1">
                            You can use variables like <code class="bg-base-300 px-1 rounded">@{{customer_name}}</code> in the subject line.
                        </p>
                    </div>
                </div>

                {{-- Body Editor --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <label class="label">
                            <span class="label-text font-medium">Email Body</span>
                        </label>
                        <div id="email-editor"></div>
                        <input type="hidden" name="body_content" id="body_content" value="{{ old('body_content', $template?->body_html ?? $defaultBody) }}" />
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--device-floppy] size-4"></span>
                        Save Template
                    </button>
                    <button type="button" onclick="previewTemplate()" class="btn btn-outline">
                        <span class="icon-[tabler--eye] size-4"></span>
                        Preview
                    </button>
                    <button type="button" onclick="sendTestEmail()" class="btn btn-outline" id="testEmailBtn">
                        <span class="icon-[tabler--send] size-4"></span>
                        Send Test Email
                    </button>
                    @if($template)
                    <button type="button" onclick="resetTemplate()" class="btn btn-ghost text-warning">
                        <span class="icon-[tabler--refresh] size-4"></span>
                        Reset to Default
                    </button>
                    @endif
                </div>
            </div>

            {{-- Variables Sidebar --}}
            <div class="xl:w-64 shrink-0">
                <div class="card bg-base-100 sticky top-6">
                    <div class="card-body">
                        <h3 class="font-semibold text-sm flex items-center gap-2">
                            <span class="icon-[tabler--variable] size-4"></span>
                            Available Variables
                        </h3>
                        <p class="text-xs text-base-content/60 mb-3">Click to insert into subject or copy to paste into body</p>

                        <div class="space-y-2">
                            @foreach($templateConfig['variables'] as $varName => $varDescription)
                            <div class="variable-chip badge badge-soft badge-primary gap-1 w-full justify-start py-2 px-3"
                                 onclick="insertVariable('{{ $varName }}')"
                                 data-tooltip="{{ $varDescription }}">
                                <span class="icon-[tabler--code] size-3"></span>
                                <span class="font-mono text-xs">&#123;&#123;{{ $varName }}&#125;&#125;</span>
                            </div>
                            @endforeach
                        </div>

                        <div class="divider text-xs">How to Use</div>
                        <ul class="text-xs text-base-content/70 space-y-1">
                            <li class="flex items-start gap-1">
                                <span class="icon-[tabler--point-filled] size-3 shrink-0 mt-0.5"></span>
                                Click a variable to insert at cursor position in subject
                            </li>
                            <li class="flex items-start gap-1">
                                <span class="icon-[tabler--point-filled] size-3 shrink-0 mt-0.5"></span>
                                Type <code class="bg-base-300 px-0.5 rounded">@{{variable}}</code> directly in the editor
                            </li>
                            <li class="flex items-start gap-1">
                                <span class="icon-[tabler--point-filled] size-3 shrink-0 mt-0.5"></span>
                                Variables will be replaced with actual values when email is sent
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Preview Modal --}}
<div id="previewModal" class="fixed inset-0 z-50 items-center justify-center p-4" style="display: none;">
    <div class="fixed inset-0 bg-black/50" onclick="closePreviewModal()"></div>
    <div class="relative bg-base-100 rounded-box shadow-2xl flex flex-col z-10 w-full max-w-5xl h-[98vh]">
        <div class="flex items-center justify-between p-4 border-b border-base-content/10">
            <h3 class="font-semibold" id="previewTitle">Email Preview</h3>
            <button onclick="closePreviewModal()" class="btn btn-ghost btn-sm btn-square">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>
        <div class="flex-1 overflow-auto p-4">
            <div id="previewLoading" class="flex items-center justify-center h-full">
                <span class="loading loading-spinner loading-lg"></span>
            </div>
            <iframe id="previewFrame" class="w-full h-full border-0 rounded-lg bg-white" style="min-height: 700px; display: none;"></iframe>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
var quill;
var subjectInput = document.getElementById('subject');

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill
    quill = new Quill('#email-editor', {
        theme: 'snow',
        placeholder: 'Compose your email content...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link'],
                ['clean']
            ]
        }
    });

    // Set initial content from hidden input
    var initialContent = document.getElementById('body_content').value;
    if (initialContent && initialContent.trim() !== '') {
        quill.root.innerHTML = initialContent;
    }

    // Sync content before form submission
    document.getElementById('templateForm').addEventListener('submit', function() {
        document.getElementById('body_content').value = quill.root.innerHTML;
    });
});

function insertVariable(varName) {
    var variable = '{{' + varName + '}}';

    // If subject input is focused, insert there
    if (document.activeElement === subjectInput) {
        var start = subjectInput.selectionStart;
        var end = subjectInput.selectionEnd;
        var text = subjectInput.value;
        subjectInput.value = text.substring(0, start) + variable + text.substring(end);
        subjectInput.selectionStart = subjectInput.selectionEnd = start + variable.length;
        subjectInput.focus();
    } else {
        // Copy to clipboard and show notification
        navigator.clipboard.writeText(variable).then(function() {
            showToast('Variable copied! Paste it in the editor.', 'info');
        });
    }
}

function previewTemplate() {
    var modal = document.getElementById('previewModal');
    var frame = document.getElementById('previewFrame');
    var loading = document.getElementById('previewLoading');
    var title = document.getElementById('previewTitle');

    modal.style.display = 'flex';
    frame.style.display = 'none';
    loading.style.display = 'flex';
    loading.innerHTML = '<span class="loading loading-spinner loading-lg"></span>';

    var subject = document.getElementById('subject').value;
    var bodyContent = quill.root.innerHTML;

    fetch('{{ route("settings.communication.email-templates.preview", $key) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            subject: subject,
            body_content: bodyContent
        })
    })
    .then(response => response.json())
    .then(data => {
        loading.style.display = 'none';
        frame.style.display = 'block';
        frame.srcdoc = data.html;
        title.textContent = 'Preview: ' + data.subject;
    })
    .catch(error => {
        loading.innerHTML = '<div class="text-error">Failed to load preview</div>';
        console.error('Preview error:', error);
    });
}

function closePreviewModal() {
    document.getElementById('previewModal').style.display = 'none';
}

function sendTestEmail() {
    var btn = document.getElementById('testEmailBtn');
    var originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Sending...';

    var subject = document.getElementById('subject').value;
    var bodyContent = quill.root.innerHTML;

    fetch('{{ route("settings.communication.email-templates.test", $key) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            subject: subject,
            body_content: bodyContent
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Failed to send test email', 'error');
        }
    })
    .catch(error => {
        showToast('Failed to send test email', 'error');
        console.error('Test email error:', error);
    })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function showToast(message, type) {
    type = type || 'success';
    var toast = document.createElement('div');
    toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
    toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(function() { toast.remove(); }, 300);
    }, 3000);
}

function resetTemplate() {
    if (confirm('Reset this template to default? Your customizations will be lost.')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("settings.communication.email-templates.reset", $key) }}';

        var csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        document.body.appendChild(form);
        form.submit();
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreviewModal();
    }
});
</script>
@endpush
