@extends('backoffice.layouts.app')

@section('title', 'Newsletter Subscribers')
@section('page-title', 'Newsletter Subscribers')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-base-content/60">Manage newsletter subscribers for FitCRM updates and news.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" class="btn btn-outline" onclick="openEmbedModal()">
                <span class="icon-[tabler--code] size-5"></span>
                Embed Code
            </button>
            <a href="{{ route('backoffice.newsletter.create') }}" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Subscriber
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 text-primary size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--mail] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $subscribers->count() }}</div>
                        <div class="text-sm text-base-content/60">Total Subscribers</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 text-success size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--check] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $subscribers->where('status', 'active')->count() }}</div>
                        <div class="text-sm text-base-content/60">Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 text-info size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $subscribers->where('created_at', '>=', now()->subDays(7))->count() }}</div>
                        <div class="text-sm text-base-content/60">This Week</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscribers Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Subscribed</th>
                            <th class="w-28">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $subscriber)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary/10 text-primary size-10 rounded-full text-sm font-bold">
                                            {{ strtoupper(substr($subscriber->first_name, 0, 1) . substr($subscriber->last_name ?? '', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="font-medium">{{ $subscriber->full_name }}</div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $subscriber->email }}" class="link link-hover">{{ $subscriber->email }}</a>
                            </td>
                            <td>
                                @php
                                    $sourceBadge = match($subscriber->source) {
                                        'embed_form' => 'badge-info',
                                        'manual' => 'badge-neutral',
                                        'import' => 'badge-warning',
                                        default => 'badge-neutral'
                                    };
                                    $sourceLabel = match($subscriber->source) {
                                        'embed_form' => 'Embed Form',
                                        'manual' => 'Manual',
                                        'import' => 'Import',
                                        default => ucfirst($subscriber->source)
                                    };
                                @endphp
                                <span class="badge badge-soft badge-sm {{ $sourceBadge }}">{{ $sourceLabel }}</span>
                            </td>
                            <td>
                                @if($subscriber->status === 'active')
                                    <span class="badge badge-soft badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-soft badge-neutral badge-sm">Unsubscribed</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $subscriber->subscribed_at?->format('M d, Y') ?? $subscriber->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $subscriber->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <form action="{{ route('backoffice.newsletter.toggle-status', $subscriber) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        @if($subscriber->status === 'active')
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-warning" title="Unsubscribe">
                                                <span class="icon-[tabler--bell-off] size-4"></span>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Resubscribe">
                                                <span class="icon-[tabler--bell] size-4"></span>
                                            </button>
                                        @endif
                                    </form>
                                    <form action="{{ route('backoffice.newsletter.destroy', $subscriber) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this subscriber?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--mail] size-12 opacity-30"></span>
                                    <p>No subscribers yet</p>
                                    <a href="{{ route('backoffice.newsletter.create') }}" class="btn btn-primary btn-sm mt-2">
                                        Add First Subscriber
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
    @include('backoffice.newsletter._embed-modal')
@endpush

@push('scripts')
<script>
function openEmbedModal() {
    var embedModal = document.getElementById('newsletter-embed-modal');
    if (embedModal) {
        embedModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeEmbedModal() {
    var embedModal = document.getElementById('newsletter-embed-modal');
    if (embedModal) {
        embedModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

function copyToClipboard(elementId) {
    var element = document.getElementById(elementId);
    if (!element) return;

    element.select();
    element.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(element.value).then(function() {
        var container = element.closest('.relative') || element.parentElement;
        var btn = container.querySelector('button[onclick*="copyToClipboard"]');
        if (btn) {
            var originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="icon-[tabler--check] size-5 text-success"></span>';
            setTimeout(function() {
                btn.innerHTML = originalHtml;
            }, 2000);
        }
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeEmbedModal();
    }
});
</script>
@endpush
