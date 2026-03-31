@extends('backoffice.layouts.app')

@section('title', 'Support Request #' . $supportRequest->id)
@section('page-title', 'Support Request #' . $supportRequest->id)

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('backoffice.support.index') }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-xl font-semibold flex items-center gap-2">
                    Support Request #{{ $supportRequest->id }}
                    <span class="badge {{ $supportRequest->status_badge_class }}">{{ $supportRequest->status_label }}</span>
                </h1>
                <p class="text-base-content/60 text-sm">Submitted {{ $supportRequest->created_at->format('F d, Y \a\t h:i A') }}</p>
            </div>
        </div>
        <form action="{{ route('backoffice.support.destroy', $supportRequest) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this request?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-error btn-sm">
                <span class="icon-[tabler--trash] size-4"></span>
                Delete
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <span class="icon-[tabler--check] size-5"></span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Request Details --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Request Details</h2>
                    <div class="prose prose-sm max-w-none bg-base-200/50 p-4 rounded-lg">
                        <p class="whitespace-pre-wrap">{{ $supportRequest->note }}</p>
                    </div>
                </div>
            </div>

            {{-- Admin Response Form --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Update Status & Response</h2>
                    <form action="{{ route('backoffice.support.update-status', $supportRequest) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="label-text font-medium" for="status">Status</label>
                            <select name="status" id="status" class="select select-bordered w-full mt-1">
                                <option value="pending" {{ $supportRequest->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_progress" {{ $supportRequest->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="resolved" {{ $supportRequest->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $supportRequest->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>

                        <div>
                            <label class="label-text font-medium" for="admin_notes">Admin Notes / Response</label>
                            <textarea name="admin_notes" id="admin_notes" class="textarea textarea-bordered w-full mt-1" rows="5" placeholder="Add notes or response to the studio...">{{ $supportRequest->admin_notes }}</textarea>
                            <p class="text-xs text-base-content/50 mt-1">This response will be visible to the studio.</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="btn btn-primary">
                                <span class="icon-[tabler--check] size-4"></span>
                                Update Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Studio Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Studio Information</h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--building] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Studio</p>
                                <p class="font-medium">{{ $supportRequest->host->studio_name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--id] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Host ID</p>
                                <p class="font-medium">{{ $supportRequest->host_id }}</p>
                            </div>
                        </div>
                        @if($supportRequest->host)
                        <a href="{{ route('backoffice.clients.show', $supportRequest->host) }}" class="btn btn-ghost btn-sm w-full mt-2">
                            <span class="icon-[tabler--external-link] size-4"></span>
                            View Client
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Contact Information</h2>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--user] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Name</p>
                                <p class="font-medium">{{ $supportRequest->full_name }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--mail] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Email</p>
                                <a href="mailto:{{ $supportRequest->email }}" class="font-medium link link-primary">{{ $supportRequest->email }}</a>
                            </div>
                        </div>
                        @if($supportRequest->phone)
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--phone] size-5 text-base-content/50"></span>
                            <div>
                                <p class="text-xs text-base-content/50">Phone</p>
                                <a href="tel:{{ $supportRequest->phone }}" class="font-medium link link-primary">{{ $supportRequest->phone }}</a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="card bg-base-100">
                <div class="card-body">
                    <h2 class="font-semibold mb-4">Timeline</h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--calendar-plus] size-4 text-base-content/50"></span>
                            <div>
                                <p class="text-base-content/50">Created</p>
                                <p>{{ $supportRequest->created_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--calendar-event] size-4 text-base-content/50"></span>
                            <div>
                                <p class="text-base-content/50">Last Updated</p>
                                <p>{{ $supportRequest->updated_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @if($supportRequest->resolved_at)
                        <div class="flex items-center gap-3">
                            <span class="icon-[tabler--calendar-check] size-4 text-success"></span>
                            <div>
                                <p class="text-base-content/50">Resolved</p>
                                <p class="text-success">{{ $supportRequest->resolved_at->format('M d, Y h:i A') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
