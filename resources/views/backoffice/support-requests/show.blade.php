@extends('backoffice.layouts.app')

@section('title', 'Support Request Details')
@section('page-title', 'Support Request Details')

@section('content')
<div class="space-y-6">
    {{-- Back button --}}
    <div>
        <a href="{{ route('backoffice.support-requests.index') }}" class="btn btn-ghost btn-sm gap-2">
            <span class="icon-[tabler--arrow-left] size-4"></span>
            Back to Support Requests
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Contact Information --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold text-lg mb-4">Contact Information</h3>

                    <div class="flex items-start gap-4">
                        <div class="avatar avatar-placeholder">
                            <div class="bg-primary/10 text-primary size-16 rounded-full text-xl font-bold">
                                {{ strtoupper(substr($supportRequest->first_name, 0, 1) . substr($supportRequest->last_name ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-xl font-semibold">
                                {{ $supportRequest->first_name }} {{ $supportRequest->last_name }}
                            </h4>
                            <div class="mt-2 space-y-1">
                                <p class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--mail] size-4 text-base-content/50"></span>
                                    <a href="mailto:{{ $supportRequest->email }}" class="link link-primary">{{ $supportRequest->email }}</a>
                                </p>
                                @if($supportRequest->phone)
                                <p class="flex items-center gap-2 text-sm">
                                    <span class="icon-[tabler--phone] size-4 text-base-content/50"></span>
                                    <a href="tel:{{ $supportRequest->phone }}" class="link">{{ $supportRequest->phone }}</a>
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Request Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold text-lg mb-4">Request Details</h3>

                    @if($supportRequest->note)
                    <div class="mb-6">
                        <label class="text-sm font-medium text-base-content/70">Message from User</label>
                        <div class="mt-2 p-4 bg-base-200 rounded-lg">
                            <p class="whitespace-pre-wrap">{{ $supportRequest->note }}</p>
                        </div>
                    </div>
                    @else
                    <p class="text-base-content/50 italic">No message provided.</p>
                    @endif

                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Source</label>
                            <p class="mt-1 capitalize">{{ $supportRequest->source }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Submitted</label>
                            <p class="mt-1">{{ $supportRequest->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>

                    @if($supportRequest->metadata && isset($supportRequest->metadata['onboarding_step']))
                    <div class="mt-4">
                        <label class="text-sm font-medium text-base-content/70">Onboarding Step at Request</label>
                        <p class="mt-1">Step {{ $supportRequest->metadata['onboarding_step'] }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Studio Information --}}
            @if($supportRequest->host)
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold text-lg mb-4">Studio Information</h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Studio Name</label>
                            <p class="mt-1">{{ $supportRequest->host->studio_name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Subdomain</label>
                            <p class="mt-1">{{ $supportRequest->host->subdomain ?? 'Not set' }}</p>
                        </div>
                        @if($supportRequest->host->studio_structure)
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Structure</label>
                            <p class="mt-1 capitalize">{{ str_replace('_', ' ', $supportRequest->host->studio_structure) }}</p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-base-content/70">Created</label>
                            <p class="mt-1">{{ $supportRequest->host->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('backoffice.clients.show', $supportRequest->host) }}"
                           class="btn btn-sm btn-outline">
                            <span class="icon-[tabler--building] size-4"></span>
                            View Client Details
                        </a>
                    </div>
                </div>
            </div>
            @endif

            {{-- Admin Notes --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold text-lg mb-4">Admin Notes</h3>

                    @php
                        $adminNotes = $supportRequest->metadata['admin_notes'] ?? [];
                    @endphp

                    @if(count($adminNotes) > 0)
                    <div class="space-y-4 mb-6">
                        @foreach($adminNotes as $note)
                        <div class="p-4 bg-base-200/50 rounded-lg border-l-4 border-primary">
                            <p class="whitespace-pre-wrap">{{ $note['note'] }}</p>
                            <div class="mt-2 text-xs text-base-content/50">
                                {{ $note['admin_name'] }} &bull; {{ \Carbon\Carbon::parse($note['created_at'])->format('M d, Y g:i A') }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-base-content/50 text-sm mb-4">No notes yet.</p>
                    @endif

                    <form action="{{ route('backoffice.support-requests.add-note', $supportRequest) }}" method="POST">
                        @csrf
                        <div class="form-control">
                            <label for="note" class="label label-text">Add a Note</label>
                            <textarea id="note" name="note" class="textarea textarea-bordered" rows="3"
                                      placeholder="Add internal notes about this request..." required></textarea>
                        </div>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <span class="icon-[tabler--note] size-4"></span>
                                Add Note
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Status Card --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Status</h3>

                    @php
                        $statusClass = match($supportRequest->status) {
                            'pending' => 'badge-warning',
                            'in_progress' => 'badge-info',
                            'resolved' => 'badge-success',
                            default => 'badge-ghost',
                        };
                    @endphp

                    <div class="flex items-center gap-3 mb-4">
                        <span class="badge {{ $statusClass }} badge-lg capitalize">
                            {{ str_replace('_', ' ', $supportRequest->status) }}
                        </span>
                    </div>

                    <form action="{{ route('backoffice.support-requests.update-status', $supportRequest) }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="form-control mb-4">
                            <label for="status" class="label label-text">Change Status</label>
                            <select id="status" name="status" class="select select-bordered w-full">
                                <option value="pending" {{ $supportRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $supportRequest->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $supportRequest->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>

                        <div class="form-control mb-4" id="resolution-notes-container" style="{{ $supportRequest->status !== 'resolved' ? 'display: none;' : '' }}">
                            <label for="resolution_notes" class="label label-text">Resolution Notes</label>
                            <textarea id="resolution_notes" name="resolution_notes" class="textarea textarea-bordered" rows="2"
                                      placeholder="How was this resolved?">{{ $supportRequest->resolution_notes }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            Update Status
                        </button>
                    </form>

                    @if($supportRequest->resolved_at)
                    <div class="mt-4 pt-4 border-t border-base-content/10">
                        <p class="text-sm text-base-content/70">
                            <strong>Resolved:</strong> {{ $supportRequest->resolved_at->format('M d, Y g:i A') }}
                        </p>
                        @if($supportRequest->resolvedBy)
                        <p class="text-sm text-base-content/70">
                            <strong>By:</strong> {{ $supportRequest->resolvedBy->full_name ?? 'Admin' }}
                        </p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h3 class="font-semibold mb-4">Quick Actions</h3>

                    <div class="space-y-2">
                        <a href="mailto:{{ $supportRequest->email }}" class="btn btn-outline btn-sm w-full justify-start gap-2">
                            <span class="icon-[tabler--mail] size-4"></span>
                            Send Email
                        </a>
                        @if($supportRequest->phone)
                        <a href="tel:{{ $supportRequest->phone }}" class="btn btn-outline btn-sm w-full justify-start gap-2">
                            <span class="icon-[tabler--phone] size-4"></span>
                            Call
                        </a>
                        @endif
                        @if($supportRequest->host)
                        <a href="{{ route('backoffice.clients.show', $supportRequest->host) }}" class="btn btn-outline btn-sm w-full justify-start gap-2">
                            <span class="icon-[tabler--building] size-4"></span>
                            View Studio
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="card bg-base-100 border border-error/30">
                <div class="card-body">
                    <h3 class="font-semibold text-error mb-4">Danger Zone</h3>

                    <form action="{{ route('backoffice.support-requests.destroy', $supportRequest) }}" method="POST"
                          onsubmit="return confirm('Are you sure you want to delete this support request? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline btn-error btn-sm w-full">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Delete Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('status')?.addEventListener('change', function() {
    const container = document.getElementById('resolution-notes-container');
    if (this.value === 'resolved') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
});
</script>
@endpush
