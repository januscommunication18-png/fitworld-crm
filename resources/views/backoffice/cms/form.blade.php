@extends('backoffice.layouts.app')

@section('title', $isEdit ? 'Edit Page' : 'Create Page')
@section('page-title', $isEdit ? 'Edit Page' : 'Create Page')

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/quill/quill.snow.css') }}" />
<style>
#content-editor .ql-toolbar {
    border-radius: 0.5rem 0.5rem 0 0;
    border-color: hsl(var(--bc) / 0.2);
    background: hsl(var(--b2));
}
#content-editor .ql-container {
    border-radius: 0 0 0.5rem 0.5rem;
    border-color: hsl(var(--bc) / 0.2);
    min-height: 400px;
    font-size: 1rem;
}
#content-editor .ql-editor {
    min-height: 380px;
}
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.cms.index') }}" class="btn btn-ghost btn-sm btn-square">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">{{ $isEdit ? 'Edit' : 'Create' }} {{ \App\Models\CmsPage::getTypes()[$page->type] ?? 'Page' }}</h1>
            <p class="text-base-content/60 mt-1">{{ $isEdit ? 'Update the page content and settings' : 'Create a new CMS page' }}</p>
        </div>
    </div>

    <form action="{{ $isEdit ? route('backoffice.cms.update', $page) : route('backoffice.cms.store') }}"
          method="POST" id="page-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Title --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="form-control">
                            <label class="label" for="title">
                                <span class="label-text font-medium">Page Title <span class="text-error">*</span></span>
                            </label>
                            <input type="text"
                                   id="title"
                                   name="title"
                                   class="input input-bordered @error('title') input-error @enderror"
                                   value="{{ old('title', $page->title) }}"
                                   placeholder="Enter page title..."
                                   required>
                            @error('title')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Content Editor --}}
                <div class="card bg-base-100">
                    <div class="card-header border-b border-base-200">
                        <h2 class="card-title text-base">Page Content</h2>
                    </div>
                    <div class="card-body">
                        <div id="content-editor"></div>
                        <input type="hidden" name="content" id="content" value="{{ old('content', $page->content) }}">
                        @error('content')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Publish Settings --}}
                <div class="card bg-base-100">
                    <div class="card-header border-b border-base-200">
                        <h2 class="card-title text-base">Publish</h2>
                    </div>
                    <div class="card-body space-y-4">
                        {{-- Page Type --}}
                        <div class="form-control">
                            <label class="label" for="type">
                                <span class="label-text font-medium">Page Type</span>
                            </label>
                            <select name="type" id="type" class="select select-bordered" {{ $isEdit ? 'disabled' : '' }}>
                                @foreach(\App\Models\CmsPage::getTypes() as $typeKey => $typeLabel)
                                <option value="{{ $typeKey }}" {{ old('type', $page->type) === $typeKey ? 'selected' : '' }}>
                                    {{ $typeLabel }}
                                </option>
                                @endforeach
                            </select>
                            @if($isEdit)
                            <input type="hidden" name="type" value="{{ $page->type }}">
                            @endif
                        </div>

                        {{-- Status --}}
                        <div class="form-control">
                            <label class="label" for="status">
                                <span class="label-text font-medium">Status</span>
                            </label>
                            <select name="status" id="status" class="select select-bordered">
                                @foreach(\App\Models\CmsPage::getStatuses() as $statusKey => $statusLabel)
                                <option value="{{ $statusKey }}" {{ old('status', $page->status) === $statusKey ? 'selected' : '' }}>
                                    {{ $statusLabel }}
                                </option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-base-content/50">
                                    Setting to "Active" will deactivate other pages of the same type
                                </span>
                            </label>
                        </div>

                        @if($isEdit && $page->published_at)
                        <div class="text-sm text-base-content/60">
                            <span class="icon-[tabler--clock] size-4 inline-block mr-1"></span>
                            Published: {{ $page->published_at->format('M d, Y \a\t h:i A') }}
                        </div>
                        @endif

                        <div class="divider my-2"></div>

                        {{-- Actions --}}
                        <div class="flex flex-col gap-2">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--device-floppy] size-5"></span>
                                {{ $isEdit ? 'Update Page' : 'Create Page' }}
                            </button>
                            <a href="{{ route('backoffice.cms.index') }}" class="btn btn-ghost">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Info --}}
                <div class="card bg-base-100">
                    <div class="card-body">
                        <div class="flex items-start gap-3">
                            <span class="icon-[tabler--info-circle] size-5 text-info shrink-0 mt-0.5"></span>
                            <div class="text-sm text-base-content/70">
                                <p class="font-medium text-base-content mb-1">Tips</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Use clear headings for better readability</li>
                                    <li>Keep legal language simple when possible</li>
                                    <li>Update the "Last Updated" date in your content</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                @if($isEdit)
                {{-- Meta Info --}}
                <div class="card bg-base-100">
                    <div class="card-body text-sm text-base-content/60 space-y-2">
                        <div class="flex justify-between">
                            <span>Created:</span>
                            <span>{{ $page->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Last Updated:</span>
                            <span>{{ $page->updated_at->format('M d, Y') }}</span>
                        </div>
                        @if($page->createdBy)
                        <div class="flex justify-between">
                            <span>Created By:</span>
                            <span>{{ $page->createdBy->first_name }} {{ $page->createdBy->last_name }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('vendor/quill/quill.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editor
    var quill = new Quill('#content-editor', {
        theme: 'snow',
        placeholder: 'Write your page content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link'],
                ['blockquote'],
                ['clean']
            ]
        }
    });

    // Load existing content
    var existingContent = document.getElementById('content').value;
    if (existingContent) {
        quill.root.innerHTML = existingContent;
    }

    // Update hidden field on form submit
    document.getElementById('page-form').addEventListener('submit', function(e) {
        var content = quill.root.innerHTML;
        // Don't save if content is just empty paragraph
        if (content === '<p><br></p>') {
            content = '';
        }
        document.getElementById('content').value = content;
    });
});
</script>
@endpush
@endsection
