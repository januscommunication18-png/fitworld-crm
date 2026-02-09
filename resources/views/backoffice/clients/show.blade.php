@extends('backoffice.layouts.app')

@section('title', $client->studio_name ?? 'Client Details')
@section('page-title', 'Client Details')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.clients.index') }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Clients
    </a>

    {{-- Client Header Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex flex-col md:flex-row gap-6">
                {{-- Logo/Avatar --}}
                <div class="flex-shrink-0">
                    @if($client->logo)
                        <img src="{{ $client->logo }}" alt="{{ $client->studio_name }}" class="w-24 h-24 rounded-xl object-cover">
                    @else
                        <div class="avatar avatar-placeholder">
                            <div class="bg-primary/10 text-primary w-24 h-24 rounded-xl text-3xl font-bold">
                                {{ strtoupper(substr($client->studio_name ?? 'S', 0, 2)) }}
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Client Info --}}
                <div class="flex-1">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-bold">{{ $client->studio_name ?? 'Unnamed Studio' }}</h2>
                            <p class="text-base-content/60">{{ $client->subdomain }}.fitcrm.app</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'active' => 'badge-success',
                                    'inactive' => 'badge-neutral',
                                    'pending_verify' => 'badge-warning',
                                    'suspended' => 'badge-error',
                                ];
                            @endphp
                            <span class="badge badge-soft {{ $statusColors[$client->status] ?? 'badge-neutral' }} capitalize">
                                {{ str_replace('_', ' ', $client->status ?? 'pending') }}
                            </span>
                            @if($client->verified_at)
                                <span class="badge badge-soft badge-success">
                                    <span class="icon-[tabler--check] size-3 mr-1"></span>
                                    Verified
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Owner Info --}}
                    @if($client->owner)
                    <div class="mt-4 flex items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--user] size-4 text-base-content/40"></span>
                            <span>{{ $client->owner->first_name }} {{ $client->owner->last_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--mail] size-4 text-base-content/40"></span>
                            <a href="mailto:{{ $client->owner->email }}" class="text-primary hover:underline">{{ $client->owner->email }}</a>
                        </div>
                        @if($client->phone)
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--phone] size-4 text-base-content/40"></span>
                            <span>{{ $client->phone }}</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Quick Actions --}}
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('backoffice.clients.edit', $client) }}" class="btn btn-sm btn-primary">
                            <span class="icon-[tabler--edit] size-4"></span>
                            Edit
                        </a>
                        <button type="button" class="btn btn-sm btn-soft btn-secondary" onclick="openStatusModal()">
                            <span class="icon-[tabler--toggle-left] size-4"></span>
                            Change Status
                        </button>
                        @if(!$client->verified_at && $client->owner)
                        <form action="{{ route('backoffice.clients.resend-verification', $client) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-soft btn-info">
                                <span class="icon-[tabler--mail-forward] size-4"></span>
                                Resend Verification
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Plan</div>
                <div class="font-semibold">
                    @if($client->plan)
                        {{ $client->plan->name }}
                    @else
                        <span class="text-base-content/40">No plan assigned</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Registered</div>
                <div class="font-semibold">{{ $client->created_at->format('M d, Y') }}</div>
                <div class="text-xs text-base-content/60">{{ $client->created_at->diffForHumans() }}</div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Verified</div>
                <div class="font-semibold">
                    @if($client->verified_at)
                        {{ $client->verified_at->format('M d, Y') }}
                    @else
                        <span class="text-warning">Not verified</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Content Tabs --}}
    <div class="card bg-base-100">
        <div class="card-header border-b border-base-content/10">
            <div class="tabs">
                <button type="button" class="tab tab-active" data-tab="users">
                    Users
                    <span class="badge badge-neutral badge-sm ml-2">{{ $counts['users'] }}</span>
                </button>
                <button type="button" class="tab" data-tab="classes">
                    Classes
                    <span class="badge badge-neutral badge-sm ml-2">{{ $counts['classes'] }}</span>
                </button>
                <button type="button" class="tab" data-tab="instructors">
                    Instructors
                    <span class="badge badge-neutral badge-sm ml-2">{{ $counts['instructors'] }}</span>
                </button>
                <button type="button" class="tab" data-tab="history">
                    Status History
                </button>
                <button type="button" class="tab" data-tab="settings">
                    Settings
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Users Tab --}}
            <div id="tab-users" class="tab-content">
                @if($client->users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->users as $user)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-placeholder">
                                            <div class="bg-base-200 text-base-content size-8 rounded-full text-xs font-medium">
                                                {{ strtoupper(substr($user->first_name ?? '', 0, 1) . substr($user->last_name ?? '', 0, 1)) }}
                                            </div>
                                        </div>
                                        <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $user->role ?? 'member' }}</span></td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8 text-base-content/60">
                    <span class="icon-[tabler--users] size-12 opacity-30 mb-2"></span>
                    <p>No team members found</p>
                </div>
                @endif
            </div>

            {{-- Classes Tab --}}
            <div id="tab-classes" class="tab-content hidden">
                <div class="text-center py-8 text-base-content/60">
                    <span class="icon-[tabler--yoga] size-12 opacity-30 mb-2"></span>
                    <p>Class management coming soon</p>
                </div>
            </div>

            {{-- Instructors Tab --}}
            <div id="tab-instructors" class="tab-content hidden">
                <div class="text-center py-8 text-base-content/60">
                    <span class="icon-[tabler--user-star] size-12 opacity-30 mb-2"></span>
                    <p>Instructor management coming soon</p>
                </div>
            </div>

            {{-- Status History Tab --}}
            <div id="tab-history" class="tab-content hidden">
                @if($client->statusHistory->count() > 0)
                <div class="space-y-4">
                    @foreach($client->statusHistory as $history)
                    <div class="flex items-start gap-4 pb-4 border-b border-base-content/10 last:border-0">
                        <div class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center shrink-0">
                            <span class="icon-[tabler--history] size-5 text-base-content/40"></span>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ str_replace('_', ' ', $history->old_status ?? 'new') }}</span>
                                <span class="icon-[tabler--arrow-right] size-4 text-base-content/40"></span>
                                <span class="badge badge-soft badge-primary badge-sm capitalize">{{ str_replace('_', ' ', $history->new_status) }}</span>
                            </div>
                            @if($history->reason)
                            <p class="text-sm text-base-content/60 mt-1">{{ $history->reason }}</p>
                            @endif
                            <p class="text-xs text-base-content/40 mt-1">
                                {{ $history->created_at->format('M d, Y h:i A') }}
                                @if($history->adminUser)
                                    by {{ $history->adminUser->first_name }} {{ $history->adminUser->last_name }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8 text-base-content/60">
                    <span class="icon-[tabler--history] size-12 opacity-30 mb-2"></span>
                    <p>No status changes recorded</p>
                </div>
                @endif
            </div>

            {{-- Settings Tab --}}
            <div id="tab-settings" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-medium mb-4">Studio Settings</h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Subdomain</dt>
                                <dd class="font-medium">{{ $client->subdomain }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Timezone</dt>
                                <dd class="font-medium">{{ $client->timezone ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Phone</dt>
                                <dd class="font-medium">{{ $client->phone ?? 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h4 class="font-medium mb-4">Subscription</h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Subscription Status</dt>
                                <dd><span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $client->subscription_status ?? 'none' }}</span></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Trial Ends</dt>
                                <dd class="font-medium">{{ $client->trial_ends_at ? $client->trial_ends_at->format('M d, Y') : 'N/A' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-base-content/60">Subscription Ends</dt>
                                <dd class="font-medium">{{ $client->subscription_ends_at ? $client->subscription_ends_at->format('M d, Y') : 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status Change Modal --}}
<dialog id="status-modal" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </form>
        <h3 class="font-bold text-lg mb-4">Change Client Status</h3>
        <form action="{{ route('backoffice.clients.status', $client) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="status">New Status</label>
                    <select name="status" id="status" class="select w-full" required>
                        <option value="active" {{ $client->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $client->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="pending_verify" {{ $client->status === 'pending_verify' ? 'selected' : '' }}>Pending Verify</option>
                        <option value="suspended" {{ $client->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div>
                    <label class="label-text" for="reason">Reason (optional)</label>
                    <textarea name="reason" id="reason" class="textarea w-full" rows="3"
                        placeholder="Reason for status change..."></textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('status-modal').close()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Status</button>
                </div>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
function openStatusModal() {
    document.getElementById('status-modal').showModal();
}

// Tab switching
document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('[data-tab]');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var targetId = 'tab-' + this.dataset.tab;

            // Update tab states
            tabs.forEach(function(t) { t.classList.remove('tab-active'); });
            this.classList.add('tab-active');

            // Update content visibility
            document.querySelectorAll('.tab-content').forEach(function(content) {
                content.classList.add('hidden');
            });
            document.getElementById(targetId).classList.remove('hidden');
        });
    });
});
</script>
@endsection
