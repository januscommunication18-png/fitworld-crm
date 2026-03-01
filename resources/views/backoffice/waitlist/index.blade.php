@extends('backoffice.layouts.app')

@section('title', 'Waitlist')
@section('page-title', 'Waitlist')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <p class="text-base-content/60">Manage prospect waitlist entries for FitCRM.</p>
        </div>
        <div class="flex gap-2">
            <button type="button" class="btn btn-outline" id="btn-embed-code">
                <span class="icon-[tabler--code] size-5"></span>
                Embed Code
            </button>
            <button type="button" class="btn btn-primary" id="btn-add-waitlist">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Waitlist
            </button>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 text-primary size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--users] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $waitlists->count() }}</div>
                        <div class="text-sm text-base-content/60">Total Signups</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 text-success size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--calendar-plus] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $waitlists->where('created_at', '>=', now()->subDays(7))->count() }}</div>
                        <div class="text-sm text-base-content/60">This Week</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 text-info size-10 rounded-full flex items-center justify-center">
                        <span class="icon-[tabler--building] size-5"></span>
                    </div>
                    <div>
                        <div class="text-2xl font-bold">{{ $waitlists->whereNotNull('studio_name')->count() }}</div>
                        <div class="text-sm text-base-content/60">With Studio Name</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Waitlist Table --}}
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Studio Name</th>
                            <th>Studio Type</th>
                            <th>Members</th>
                            <th>Signed Up</th>
                            <th class="w-20">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($waitlists as $entry)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar avatar-placeholder">
                                        <div class="bg-primary/10 text-primary size-10 rounded-full text-sm font-bold">
                                            {{ strtoupper(substr($entry->first_name, 0, 1) . substr($entry->last_name ?? '', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="font-medium">{{ $entry->full_name }}</div>
                                </div>
                            </td>
                            <td>
                                <a href="mailto:{{ $entry->email }}" class="link link-hover">{{ $entry->email }}</a>
                            </td>
                            <td>{{ $entry->studio_name ?? '-' }}</td>
                            <td>
                                @if($entry->studio_type && count($entry->studio_type) > 0)
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($entry->studio_type as $type)
                                            <span class="badge badge-soft badge-neutral badge-xs">{{ \App\Models\ProspectWaitlist::STUDIO_TYPES[$type] ?? $type }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                @if($entry->member_size)
                                    <span class="text-sm">{{ $entry->member_size_label }}</span>
                                @else
                                    <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $entry->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $entry->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <form action="{{ route('backoffice.waitlist.destroy', $entry) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Are you sure you want to delete this waitlist entry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center gap-2 text-base-content/60">
                                    <span class="icon-[tabler--list] size-12 opacity-30"></span>
                                    <p>No waitlist entries yet</p>
                                    <button type="button" class="btn btn-primary btn-sm mt-2 btn-open-waitlist-modal">
                                        Add First Entry
                                    </button>
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
    @include('backoffice.waitlist._modal')
    @include('backoffice.waitlist._embed-modal')
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Waitlist Modal
        const modal = document.getElementById('add-waitlist-modal');
        const btnAdd = document.getElementById('btn-add-waitlist');
        const btnsOpen = document.querySelectorAll('.btn-open-waitlist-modal');

        // Embed Code Modal
        const embedModal = document.getElementById('embed-code-modal');
        const btnEmbed = document.getElementById('btn-embed-code');

        if (!modal) {
            console.error('Waitlist modal not found');
            return;
        }

        // Add Waitlist Modal Functions
        function openModal() {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Embed Modal Functions
        function openEmbedModal() {
            embedModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeEmbedModal() {
            embedModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Copy to clipboard function
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999);
            navigator.clipboard.writeText(element.value).then(function() {
                // Show success feedback
                const btn = element.nextElementSibling;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<span class="icon-[tabler--check] size-5 text-success"></span>';
                setTimeout(function() {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        }

        // Expose to global scope for onclick handlers in modals
        window.openWaitlistModal = openModal;
        window.closeWaitlistModal = closeModal;
        window.openEmbedModal = openEmbedModal;
        window.closeEmbedModal = closeEmbedModal;
        window.copyToClipboard = copyToClipboard;

        // Button click handlers
        if (btnAdd) {
            btnAdd.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        }

        if (btnEmbed) {
            btnEmbed.addEventListener('click', function(e) {
                e.preventDefault();
                openEmbedModal();
            });
        }

        btnsOpen.forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                openModal();
            });
        });

        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (!modal.classList.contains('hidden')) {
                    closeModal();
                }
                if (!embedModal.classList.contains('hidden')) {
                    closeEmbedModal();
                }
            }
        });

        // Open modal if there are validation errors
        @if($errors->any())
            openModal();
        @endif
    });
</script>
@endpush
