@extends('backoffice.layouts.app')

@section('title', 'CMS Pages')
@section('page-title', 'CMS Pages')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">CMS Pages</h1>
            <p class="text-base-content/60 mt-1">Manage your Terms & Conditions and Privacy Policy pages</p>
        </div>
        <a href="{{ route('backoffice.cms.create') }}" class="btn btn-primary">
            <span class="icon-[tabler--plus] size-5"></span>
            Add New Page
        </a>
    </div>

    {{-- Filter Tabs --}}
    <div class="tabs tabs-bordered">
        <a href="{{ route('backoffice.cms.index') }}"
           class="tab {{ !$type ? 'tab-active' : '' }}">
            All Pages
        </a>
        @foreach(\App\Models\CmsPage::getTypes() as $typeKey => $typeLabel)
        <a href="{{ route('backoffice.cms.index', ['type' => $typeKey]) }}"
           class="tab {{ $type === $typeKey ? 'tab-active' : '' }}">
            {{ $typeLabel }}
        </a>
        @endforeach
    </div>

    {{-- Pages by Type --}}
    @forelse($pagesByType as $pageType => $typedPages)
    <div class="card bg-base-100">
        <div class="card-header border-b border-base-200">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                <h2 class="card-title">{{ \App\Models\CmsPage::getTypes()[$pageType] ?? $pageType }}</h2>
                <span class="badge badge-neutral badge-sm">{{ $typedPages->count() }} {{ Str::plural('page', $typedPages->count()) }}</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Published</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($typedPages as $page)
                        <tr class="{{ $page->isActive() ? 'bg-success/5' : '' }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    @if($page->isActive())
                                    <span class="icon-[tabler--circle-check-filled] size-5 text-success" title="Active"></span>
                                    @elseif($page->isDraft())
                                    <span class="icon-[tabler--clock] size-5 text-warning" title="Draft"></span>
                                    @else
                                    <span class="icon-[tabler--circle-x] size-5 text-base-content/30" title="Inactive"></span>
                                    @endif
                                    <div>
                                        <div class="font-medium">{{ $page->title }}</div>
                                        <div class="text-xs text-base-content/50">{{ $page->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $page->status_badge_class }} badge-soft">
                                    {{ $page->status_label }}
                                </span>
                            </td>
                            <td>
                                <div class="text-sm">{{ $page->updated_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/50">{{ $page->updated_at->format('h:i A') }}</div>
                            </td>
                            <td>
                                @if($page->published_at)
                                <div class="text-sm">{{ $page->published_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/50">{{ $page->published_at->format('h:i A') }}</div>
                                @else
                                <span class="text-base-content/40">Not published</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('backoffice.cms.edit', $page) }}"
                                       class="btn btn-sm btn-ghost btn-square" title="Edit">
                                        <span class="icon-[tabler--edit] size-4"></span>
                                    </a>
                                    @if($page->isActive())
                                    <button type="button"
                                            class="btn btn-sm btn-ghost btn-square text-warning"
                                            title="Set Inactive"
                                            onclick="changeStatus({{ $page->id }}, 'inactive')">
                                        <span class="icon-[tabler--circle-x] size-4"></span>
                                    </button>
                                    @else
                                    <button type="button"
                                            class="btn btn-sm btn-ghost btn-square text-success"
                                            title="Set Active"
                                            onclick="changeStatus({{ $page->id }}, 'active')">
                                        <span class="icon-[tabler--circle-check] size-4"></span>
                                    </button>
                                    <form action="{{ route('backoffice.cms.destroy', $page) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this page?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-ghost btn-square text-error" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <div class="w-16 h-16 bg-base-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="icon-[tabler--file-text] size-8 text-base-content/40"></span>
            </div>
            <h3 class="text-lg font-semibold">No CMS Pages Found</h3>
            <p class="text-base-content/60 mt-1">Get started by creating your first page.</p>
            <div class="mt-4">
                <a href="{{ route('backoffice.cms.create', ['type' => 'terms_conditions']) }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Create First Page
                </a>
            </div>
        </div>
    </div>
    @endforelse

    {{-- Info Card --}}
    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <div>
            <p class="font-medium">About CMS Pages</p>
            <ul class="text-sm mt-1 list-disc list-inside">
                <li>Only <strong>one active page</strong> is allowed per type (Terms & Conditions or Privacy Policy)</li>
                <li>When you set a page to "Active", any other active page of the same type will automatically become "Inactive"</li>
                <li>Draft pages are not visible to users</li>
                <li>Active pages cannot be deleted - set them to inactive first</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeStatus(pageId, status) {
    fetch(`{{ url('/backoffice/cms') }}/${pageId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to reflect changes
            window.location.reload();
        } else {
            alert(data.message || 'Failed to update status');
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}
</script>
@endpush
@endsection
