@extends('backoffice.layouts.app')

@section('title', 'Edit Client')
@section('page-title', 'Edit Client')

@section('content')
<div class="space-y-6">
    {{-- Back Link --}}
    <a href="{{ route('backoffice.clients.show', $client) }}" class="inline-flex items-center gap-2 text-sm text-base-content/60 hover:text-primary">
        <span class="icon-[tabler--arrow-left] size-4"></span>
        Back to Client Details
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Form --}}
        <div class="lg:col-span-2">
            <form action="{{ route('backoffice.clients.update', $client) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="card bg-base-100">
                    <div class="card-header">
                        <h3 class="card-title">Studio Information</h3>
                    </div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label-text" for="studio_name">Studio Name</label>
                            <input type="text" id="studio_name" name="studio_name"
                                value="{{ old('studio_name', $client->studio_name) }}"
                                class="input w-full @error('studio_name') input-error @enderror"
                                required>
                            @error('studio_name')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="subdomain">Subdomain</label>
                            <div class="join w-full">
                                <input type="text" id="subdomain" name="subdomain"
                                    value="{{ old('subdomain', $client->subdomain) }}"
                                    class="input join-item flex-1 @error('subdomain') input-error @enderror"
                                    required>
                                <span class="join-item bg-base-200 px-4 flex items-center text-base-content/60">.fitcrm.app</span>
                            </div>
                            @error('subdomain')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone"
                                value="{{ old('phone', $client->phone) }}"
                                class="input w-full @error('phone') input-error @enderror"
                                placeholder="+1 (555) 123-4567">
                            @error('phone')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="label-text" for="timezone">Timezone</label>
                            <select id="timezone" name="timezone" class="select w-full @error('timezone') input-error @enderror">
                                <option value="">Select timezone...</option>
                                @php
                                    $timezones = [
                                        'America/New_York' => 'Eastern Time (US & Canada)',
                                        'America/Chicago' => 'Central Time (US & Canada)',
                                        'America/Denver' => 'Mountain Time (US & Canada)',
                                        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
                                        'America/Anchorage' => 'Alaska',
                                        'Pacific/Honolulu' => 'Hawaii',
                                        'Europe/London' => 'London',
                                        'Europe/Paris' => 'Paris',
                                        'Europe/Berlin' => 'Berlin',
                                        'Asia/Tokyo' => 'Tokyo',
                                        'Asia/Shanghai' => 'Shanghai',
                                        'Asia/Dubai' => 'Dubai',
                                        'Australia/Sydney' => 'Sydney',
                                    ];
                                @endphp
                                @foreach($timezones as $value => $label)
                                    <option value="{{ $value }}" {{ old('timezone', $client->timezone) === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('timezone')
                                <p class="text-error text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-4"></span>
                            Save Changes
                        </button>
                        <a href="{{ route('backoffice.clients.show', $client) }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">
            {{-- Owner Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Owner</h3>
                </div>
                <div class="card-body">
                    @if($client->owner)
                    <div class="flex items-center gap-3">
                        <div class="avatar avatar-placeholder">
                            <div class="bg-primary/10 text-primary size-12 rounded-full text-sm font-bold">
                                {{ strtoupper(substr($client->owner->first_name ?? '', 0, 1) . substr($client->owner->last_name ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <div class="font-medium">{{ $client->owner->first_name }} {{ $client->owner->last_name }}</div>
                            <div class="text-sm text-base-content/60">{{ $client->owner->email }}</div>
                        </div>
                    </div>
                    @else
                    <p class="text-base-content/60">No owner assigned</p>
                    @endif
                </div>
            </div>

            {{-- Status --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Status</h3>
                </div>
                <div class="card-body">
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
                    <p class="text-sm text-base-content/60 mt-2">
                        Use the "Change Status" button on the client details page to update status.
                    </p>
                </div>
            </div>

            {{-- Plan --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Plan</h3>
                </div>
                <div class="card-body">
                    @if($client->plan)
                        <span class="badge badge-soft badge-primary">{{ $client->plan->name }}</span>
                    @else
                        <span class="text-base-content/60">No plan assigned</span>
                    @endif
                    <p class="text-sm text-base-content/60 mt-2">
                        Plan changes are managed through the subscription system.
                    </p>
                </div>
            </div>

            {{-- Dates --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Important Dates</h3>
                </div>
                <div class="card-body">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Created</dt>
                            <dd>{{ $client->created_at->format('M d, Y') }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Verified</dt>
                            <dd>{{ $client->verified_at ? $client->verified_at->format('M d, Y') : 'Not verified' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-base-content/60">Last Updated</dt>
                            <dd>{{ $client->updated_at->format('M d, Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
