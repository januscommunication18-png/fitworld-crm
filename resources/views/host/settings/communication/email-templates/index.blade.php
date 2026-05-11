@extends('layouts.settings')

@push('head')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}" />
<style>
#header-editor-container .ql-toolbar,
#footer-editor-container .ql-toolbar {
    border-radius: 0.5rem 0.5rem 0 0;
    border-color: hsl(var(--bc) / 0.2);
    background: hsl(var(--b2));
}
#header-editor-container .ql-container,
#footer-editor-container .ql-container {
    border-radius: 0 0 0.5rem 0.5rem;
    border-color: hsl(var(--bc) / 0.2);
    min-height: 80px;
    font-size: 0.875rem;
}
</style>
@endpush

@section('title', 'Email Templates — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Email Templates</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">Email Templates</h2>
            <p class="text-sm text-base-content/60 mt-1">Customize the emails sent to your clients</p>
        </div>
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

    {{-- Email Layout (Header & Footer) --}}
    @php
        $emailHeader = $host->booking_settings['email_header_html'] ?? '';
        $emailFooter = $host->booking_settings['email_footer_html'] ?? '';
    @endphp
    <div class="card bg-base-100">
        <details class="group">
            <summary class="flex items-center justify-between p-5 cursor-pointer list-none hover:bg-base-200/50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--layout] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="font-semibold">Email Layout</h3>
                        <p class="text-base-content/60 text-sm">Customize the header and footer used in all emails</p>
                    </div>
                </div>
                <span class="btn btn-sm btn-ghost gap-1 group-open:hidden">
                    <span class="icon-[tabler--pencil] size-4"></span> Customize
                </span>
                <span class="btn btn-sm btn-ghost gap-1 hidden group-open:inline-flex">
                    <span class="icon-[tabler--chevron-up] size-4"></span> Collapse
                </span>
            </summary>
            <div class="border-t border-base-content/10 p-5">
                <form action="{{ route('settings.communication.email-templates.layout') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="space-y-6">
                        {{-- Live Preview --}}
                        <div class="alert alert-info alert-soft">
                            <span class="icon-[tabler--info-circle] size-5"></span>
                            <span class="text-sm">Header and footer are shared across all email templates. Use the preview button on any template to see how it looks.</span>
                        </div>

                        {{-- Header --}}
                        <div>
                            <label class="label-text font-medium mb-2 block">
                                <span class="icon-[tabler--layout-navbar] size-4 inline-block align-middle mr-1"></span>
                                Email Header
                            </label>
                            <p class="text-xs text-base-content/60 mb-2">Displayed at the top of every email with your brand color background. Supports HTML.</p>
                            <div id="header-editor-container">
                                <div id="header-editor" style="min-height: 80px;"></div>
                            </div>
                            <input type="hidden" name="email_header_html" id="email_header_html" value="{{ $emailHeader }}" />
                            <p class="text-xs text-base-content/50 mt-1">Leave empty to use default (studio name).</p>
                        </div>

                        {{-- Footer --}}
                        <div>
                            <label class="label-text font-medium mb-2 block">
                                <span class="icon-[tabler--layout-bottombar] size-4 inline-block align-middle mr-1"></span>
                                Email Footer
                            </label>
                            <p class="text-xs text-base-content/60 mb-2">Displayed at the bottom of every email. Add contact info, social links, or legal text.</p>
                            <div id="footer-editor-container">
                                <div id="footer-editor" style="min-height: 80px;"></div>
                            </div>
                            <input type="hidden" name="email_footer_html" id="email_footer_html" value="{{ $emailFooter }}" />
                            <p class="text-xs text-base-content/50 mt-1">Leave empty to use default (studio name).</p>
                        </div>

                        <button type="submit" class="btn btn-primary btn-sm" id="save-layout-btn">
                            <span class="icon-[tabler--device-floppy] size-4"></span> Save Layout
                        </button>
                    </div>
                </form>
            </div>
        </details>
    </div>

    {{-- Templates by Category --}}
    @php
        $categories = [
            'transactional' => ['label' => 'Transactional', 'icon' => 'icon-[tabler--receipt]', 'description' => 'Emails sent after bookings and payments'],
            'automation' => ['label' => 'Automation', 'icon' => 'icon-[tabler--robot]', 'description' => 'Automated emails triggered by schedules and client activity'],
            'engagement' => ['label' => 'Client Engagement', 'icon' => 'icon-[tabler--users]', 'description' => 'Emails for intake forms and client communication'],
            'team' => ['label' => 'Team', 'icon' => 'icon-[tabler--users-group]', 'description' => 'Emails for team member invitations'],
            'authentication' => ['label' => 'Authentication', 'icon' => 'icon-[tabler--lock]', 'description' => 'Password reset and login verification emails'],
        ];
    @endphp

    @foreach($categories as $categoryKey => $category)
        @php
            $categoryTemplates = collect($templates)->filter(fn($t) => $t['category'] === $categoryKey);
        @endphp
        @if($categoryTemplates->isNotEmpty())
        <div class="card bg-base-100">
            <div class="card-body">
                <div class="flex items-center gap-3 mb-4">
                    <span class="{{ $category['icon'] }} size-5 text-primary"></span>
                    <div>
                        <h3 class="font-semibold">{{ $category['label'] }}</h3>
                        <p class="text-xs text-base-content/60">{{ $category['description'] }}</p>
                    </div>
                </div>
                <div class="divide-y divide-base-content/10">
                    @foreach($categoryTemplates as $key => $template)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-sm">{{ $template['name'] }}</span>
                                @if($template['is_customized'])
                                    <span class="badge badge-soft badge-success badge-xs">Customized</span>
                                @else
                                    <span class="badge badge-soft badge-xs">Default</span>
                                @endif
                            </div>
                            <p class="text-xs text-base-content/60 mt-0.5">{{ $template['description'] }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            {{-- Preview Button --}}
                            <button type="button"
                                    onclick="previewTemplate('{{ $key }}')"
                                    class="btn btn-ghost btn-sm btn-square"
                                    data-tooltip="Preview">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </button>

                            {{-- Edit Button --}}
                            <a href="{{ route('settings.communication.email-templates.edit', $key) }}"
                               class="btn btn-ghost btn-sm btn-square"
                               data-tooltip="Edit Template">
                                <span class="icon-[tabler--pencil] size-4"></span>
                            </a>

                            {{-- Reset Button (only if customized) --}}
                            @if($template['is_customized'])
                            <form action="{{ route('settings.communication.email-templates.reset', $key) }}"
                                  method="POST"
                                  onsubmit="return confirm('Reset this template to the default? Your customizations will be lost.')">
                                @csrf
                                <button type="submit"
                                        class="btn btn-ghost btn-sm btn-square text-warning"
                                        data-tooltip="Reset to Default">
                                    <span class="icon-[tabler--refresh] size-4"></span>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    @endforeach

    {{-- Help Card --}}
    <div class="card bg-base-200/50">
        <div class="card-body">
            <div class="flex items-start gap-3">
                <span class="icon-[tabler--info-circle] size-5 text-info shrink-0 mt-0.5"></span>
                <div>
                    <h4 class="font-medium text-sm">About Email Templates</h4>
                    <p class="text-xs text-base-content/70 mt-1">
                        Customize the subject line and body content of emails sent to your clients.
                        Use variables like <code class="bg-base-300 px-1 rounded text-xs">@{{customer_name}}</code> to personalize messages.
                        Preview your changes before saving, and send test emails to yourself to verify formatting.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

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
// Initialize Quill editors for header & footer
var headerQuill, footerQuill;

document.addEventListener('DOMContentLoaded', function() {
    var toolbarOptions = [
        ['bold', 'italic', 'underline'],
        [{ 'color': [] }],
        [{ 'align': [] }],
        ['link', 'image'],
        ['clean']
    ];

    headerQuill = new Quill('#header-editor', {
        theme: 'snow',
        placeholder: 'e.g., Your studio name, logo, or tagline...',
        modules: { toolbar: toolbarOptions }
    });

    footerQuill = new Quill('#footer-editor', {
        theme: 'snow',
        placeholder: 'e.g., Contact info, social links, unsubscribe text...',
        modules: { toolbar: toolbarOptions }
    });

    // Load existing content
    var headerContent = document.getElementById('email_header_html').value;
    var footerContent = document.getElementById('email_footer_html').value;
    if (headerContent) headerQuill.root.innerHTML = headerContent;
    if (footerContent) footerQuill.root.innerHTML = footerContent;

    // Sync before form submit
    var layoutForm = document.getElementById('save-layout-btn').closest('form');
    layoutForm.addEventListener('submit', function() {
        var headerHtml = headerQuill.root.innerHTML;
        var footerHtml = footerQuill.root.innerHTML;
        // Don't save if editor is empty (just whitespace or <p><br></p>)
        document.getElementById('email_header_html').value = headerQuill.getText().trim() ? headerHtml : '';
        document.getElementById('email_footer_html').value = footerQuill.getText().trim() ? footerHtml : '';
    });
});

function previewTemplate(key) {
    var modal = document.getElementById('previewModal');
    var frame = document.getElementById('previewFrame');
    var loading = document.getElementById('previewLoading');
    var title = document.getElementById('previewTitle');

    modal.style.display = 'flex';
    frame.style.display = 'none';
    loading.style.display = 'flex';
    loading.innerHTML = '<span class="loading loading-spinner loading-lg"></span>';

    fetch(`{{ url('/settings/communication/email-templates') }}/${key}/preview`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
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

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreviewModal();
    }
});
</script>
@endpush
