@extends('backoffice.layouts.app')

@section('title', 'Preview: ' . $emailTemplate->name)
@section('page-title', 'Email Preview')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.email-templates.edit', $emailTemplate) }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Edit Template
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Preview --}}
        <div class="lg:col-span-2">
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Preview</h3>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn btn-sm btn-ghost" onclick="toggleView('desktop')" id="btn-desktop">
                            <span class="icon-[tabler--device-desktop] size-4"></span>
                        </button>
                        <button type="button" class="btn btn-sm btn-ghost" onclick="toggleView('mobile')" id="btn-mobile">
                            <span class="icon-[tabler--device-mobile] size-4"></span>
                        </button>
                    </div>
                </div>
                <div class="card-body bg-base-200 p-4">
                    {{-- Email Header --}}
                    <div class="bg-base-100 rounded-t-lg border border-base-content/10 p-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex">
                                <span class="text-base-content/60 w-16">From:</span>
                                <span>{{ config('app.name') }} &lt;noreply@fitcrm.com&gt;</span>
                            </div>
                            <div class="flex">
                                <span class="text-base-content/60 w-16">To:</span>
                                <span>John Doe &lt;john@example.com&gt;</span>
                            </div>
                            <div class="flex">
                                <span class="text-base-content/60 w-16">Subject:</span>
                                <span class="font-medium">{{ $emailTemplate->subject }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Email Body --}}
                    <div id="preview-container" class="transition-all duration-300">
                        <iframe id="preview-frame" class="w-full bg-white rounded-b-lg border border-t-0 border-base-content/10"
                            style="height: 600px;" srcdoc="{{ $renderedHtml }}"></iframe>
                    </div>
                </div>
            </div>
        </div>

        {{-- Template Info --}}
        <div class="space-y-6">
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Template Info</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-base-content/60">Name</dt>
                            <dd class="font-medium">{{ $emailTemplate->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-base-content/60">Key</dt>
                            <dd><code class="bg-base-200 px-2 py-0.5 rounded text-xs">{{ $emailTemplate->key }}</code></dd>
                        </div>
                        <div>
                            <dt class="text-base-content/60">Category</dt>
                            <dd><span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $emailTemplate->category }}</span></dd>
                        </div>
                        <div>
                            <dt class="text-base-content/60">Status</dt>
                            <dd>
                                @if($emailTemplate->is_active)
                                    <span class="badge badge-soft badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm">Inactive</span>
                                @endif
                            </dd>
                        </div>
                        @if($emailTemplate->host)
                        <div>
                            <dt class="text-base-content/60">Client</dt>
                            <dd>{{ $emailTemplate->host->studio_name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Sample Data Used</h3>
                </div>
                <div class="card-body">
                    <div class="space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-base-content/60">user_name</span>
                            <span>John Doe</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">user_email</span>
                            <span>john@example.com</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">studio_name</span>
                            <span>Fitness Studio</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">class_name</span>
                            <span>Morning Yoga</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">class_date</span>
                            <span>{{ now()->format('F j, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-base-content/60">class_time</span>
                            <span>9:00 AM</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100">
                <div class="card-body space-y-2">
                    <a href="{{ route('backoffice.email-templates.edit', $emailTemplate) }}" class="btn btn-primary w-full">
                        <span class="icon-[tabler--edit] size-5"></span>
                        Edit Template
                    </a>
                    <form action="{{ route('backoffice.email-templates.duplicate', $emailTemplate) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-soft btn-secondary w-full">
                            <span class="icon-[tabler--copy] size-5"></span>
                            Duplicate Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleView(view) {
    var container = document.getElementById('preview-container');
    var frame = document.getElementById('preview-frame');
    var btnDesktop = document.getElementById('btn-desktop');
    var btnMobile = document.getElementById('btn-mobile');

    if (view === 'mobile') {
        container.classList.add('max-w-sm', 'mx-auto');
        btnMobile.classList.add('btn-active');
        btnDesktop.classList.remove('btn-active');
    } else {
        container.classList.remove('max-w-sm', 'mx-auto');
        btnDesktop.classList.add('btn-active');
        btnMobile.classList.remove('btn-active');
    }
}

// Set desktop as default active
document.getElementById('btn-desktop').classList.add('btn-active');
</script>
@endsection
