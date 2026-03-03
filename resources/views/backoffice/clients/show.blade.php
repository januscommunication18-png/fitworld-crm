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
                    @if($client->logo_path)
                        <img src="{{ Storage::disk(config('filesystems.uploads'))->url($client->logo_path) }}" alt="{{ $client->studio_name }}" class="w-24 h-24 rounded-xl object-cover">
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
                            @if($client->short_description)
                                <p class="text-sm text-base-content/70 mt-1">{{ $client->short_description }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
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
                            @if($client->is_live)
                                <span class="badge badge-soft badge-info">
                                    <span class="icon-[tabler--world] size-3 mr-1"></span>
                                    Live
                                </span>
                            @endif
                            @if($client->booking_page_status === 'published')
                                <span class="badge badge-soft badge-primary">
                                    <span class="icon-[tabler--calendar-check] size-3 mr-1"></span>
                                    Booking Page Published
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Owner Info --}}
                    @if($client->owner)
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
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
                        @if($client->city || $client->country)
                        <div class="flex items-center gap-2">
                            <span class="icon-[tabler--map-pin] size-4 text-base-content/40"></span>
                            <span>{{ collect([$client->city, $client->country])->filter()->join(', ') }}</span>
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
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
        <div class="card bg-base-100">
            <div class="card-body py-4">
                <div class="text-sm text-base-content/60">Onboarding</div>
                <div class="font-semibold">
                    @if($client->onboarding_completed_at)
                        <span class="text-success">Completed</span>
                    @else
                        <span class="text-warning">Step {{ $client->onboarding_step ?? 1 }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Content Tabs --}}
    <div class="card bg-base-100">
        <div class="card-header border-b border-base-content/10">
            <div class="tabs flex-wrap">
                <button type="button" class="tab tab-active" data-tab="profile">
                    <span class="icon-[tabler--building-store] size-4 mr-1"></span>
                    Profile
                </button>
                <button type="button" class="tab" data-tab="contact">
                    <span class="icon-[tabler--address-book] size-4 mr-1"></span>
                    Contact
                </button>
                <button type="button" class="tab" data-tab="clients">
                    <span class="icon-[tabler--users-group] size-4 mr-1"></span>
                    Clients
                    <span class="badge badge-primary badge-sm ml-1">{{ $counts['clients'] }}</span>
                </button>
                <button type="button" class="tab" data-tab="users">
                    <span class="icon-[tabler--users] size-4 mr-1"></span>
                    Team
                    <span class="badge badge-neutral badge-sm ml-1">{{ $counts['users'] }}</span>
                </button>
                <button type="button" class="tab" data-tab="config">
                    <span class="icon-[tabler--settings] size-4 mr-1"></span>
                    Configuration
                </button>
                <button type="button" class="tab" data-tab="subscription">
                    <span class="icon-[tabler--credit-card] size-4 mr-1"></span>
                    Subscription
                </button>
                <button type="button" class="tab" data-tab="history">
                    <span class="icon-[tabler--history] size-4 mr-1"></span>
                    History
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Profile Tab --}}
            <div id="tab-profile" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Studio Details --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--building-store] size-5 text-primary"></span>
                            Studio Details
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Studio Name</dt>
                                <dd class="font-medium">{{ $client->studio_name ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Subdomain</dt>
                                <dd class="font-medium font-mono text-primary">{{ $client->subdomain }}.fitcrm.app</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Short Description</dt>
                                <dd class="font-medium text-right max-w-xs">{{ $client->short_description ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Studio Types</dt>
                                <dd>
                                    @if($client->studio_types && count($client->studio_types) > 0)
                                        <div class="flex flex-wrap gap-1 justify-end">
                                            @foreach($client->studio_types as $type)
                                                <span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $type }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">City</dt>
                                <dd class="font-medium">{{ $client->city ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Country</dt>
                                <dd class="font-medium">{{ $client->country ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Timezone</dt>
                                <dd class="font-medium">{{ $client->timezone ?? 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- About Section --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--info-circle] size-5 text-primary"></span>
                            About
                        </h4>
                        @if($client->about)
                            <div class="prose prose-sm max-w-none bg-base-200/50 rounded-lg p-4">
                                {!! nl2br(e($client->about)) !!}
                            </div>
                        @else
                            <div class="text-base-content/40 bg-base-200/50 rounded-lg p-4">No description provided</div>
                        @endif

                        {{-- Amenities --}}
                        @if($client->amenities && count($client->amenities) > 0)
                            <h4 class="font-semibold text-lg mt-6 mb-4 flex items-center gap-2">
                                <span class="icon-[tabler--sparkles] size-5 text-primary"></span>
                                Amenities
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($client->amenities as $amenity)
                                    <span class="badge badge-soft badge-primary">{{ $amenity }}</span>
                                @endforeach
                            </div>
                        @endif

                        {{-- Cover Image --}}
                        @if($client->cover_image_path)
                            <h4 class="font-semibold text-lg mt-6 mb-4 flex items-center gap-2">
                                <span class="icon-[tabler--photo] size-5 text-primary"></span>
                                Cover Image
                            </h4>
                            <img src="{{ Storage::disk(config('filesystems.uploads'))->url($client->cover_image_path) }}"
                                 alt="Cover" class="w-full h-40 object-cover rounded-lg">
                        @endif
                    </div>
                </div>
            </div>

            {{-- Contact Tab --}}
            <div id="tab-contact" class="tab-content hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Contact Information --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--phone] size-5 text-primary"></span>
                            Contact Information
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Contact Name</dt>
                                <dd class="font-medium">{{ $client->contact_name ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Studio Email</dt>
                                <dd class="font-medium">
                                    @if($client->studio_email)
                                        <a href="mailto:{{ $client->studio_email }}" class="text-primary hover:underline">{{ $client->studio_email }}</a>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Support Email</dt>
                                <dd class="font-medium">
                                    @if($client->support_email)
                                        <a href="mailto:{{ $client->support_email }}" class="text-primary hover:underline">{{ $client->support_email }}</a>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Phone</dt>
                                <dd class="font-medium">{{ $client->phone ?? 'Not set' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Address</dt>
                                <dd class="font-medium text-right max-w-xs">{{ $client->address ?? 'Not set' }}</dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Social Links --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--brand-instagram] size-5 text-primary"></span>
                            Social Links
                        </h4>
                        @if($client->social_links && count(array_filter($client->social_links)) > 0)
                            <div class="space-y-3">
                                @foreach($client->social_links as $platform => $url)
                                    @if($url)
                                        <div class="flex items-center gap-3 py-2 border-b border-base-content/5">
                                            @php
                                                $iconMap = [
                                                    'instagram' => 'brand-instagram',
                                                    'facebook' => 'brand-facebook',
                                                    'tiktok' => 'brand-tiktok',
                                                    'website' => 'world',
                                                    'twitter' => 'brand-x',
                                                    'youtube' => 'brand-youtube',
                                                ];
                                            @endphp
                                            <span class="icon-[tabler--{{ $iconMap[$platform] ?? 'link' }}] size-5 text-base-content/60"></span>
                                            <span class="capitalize text-sm text-base-content/60 w-20">{{ $platform }}</span>
                                            <a href="{{ $url }}" target="_blank" class="text-primary hover:underline text-sm truncate flex-1">{{ $url }}</a>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-base-content/40 bg-base-200/50 rounded-lg p-4">No social links configured</div>
                        @endif

                        {{-- Owner Details --}}
                        @if($client->owner)
                        <h4 class="font-semibold text-lg mt-6 mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--user-star] size-5 text-primary"></span>
                            Account Owner
                        </h4>
                        <div class="bg-base-200/50 rounded-lg p-4 space-y-2">
                            <div class="flex items-center gap-3">
                                <div class="avatar avatar-placeholder">
                                    <div class="bg-primary text-primary-content size-10 rounded-full text-sm font-bold">
                                        {{ strtoupper(substr($client->owner->first_name ?? '', 0, 1) . substr($client->owner->last_name ?? '', 0, 1)) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium">{{ $client->owner->first_name }} {{ $client->owner->last_name }}</div>
                                    <a href="mailto:{{ $client->owner->email }}" class="text-sm text-primary hover:underline">{{ $client->owner->email }}</a>
                                </div>
                            </div>
                            @if($client->owner->phone)
                                <div class="text-sm text-base-content/60 flex items-center gap-2 ml-13">
                                    <span class="icon-[tabler--phone] size-4"></span>
                                    {{ $client->owner->phone }}
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Clients Tab (Studio's Members/Students) --}}
            <div id="tab-clients" class="tab-content hidden">
                @if($studioClients->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Memberships</th>
                                <th>Bookings</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studioClients as $studioClient)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-placeholder">
                                            <div class="bg-base-200 text-base-content size-8 rounded-full text-xs font-medium">
                                                {{ strtoupper(substr($studioClient->first_name ?? '', 0, 1) . substr($studioClient->last_name ?? '', 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-medium">{{ $studioClient->first_name }} {{ $studioClient->last_name }}</span>
                                            @if($studioClient->is_vip)
                                                <span class="badge badge-soft badge-warning badge-xs ml-1">VIP</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($studioClient->email)
                                        <a href="mailto:{{ $studioClient->email }}" class="text-primary hover:underline text-sm">{{ $studioClient->email }}</a>
                                    @else
                                        <span class="text-base-content/40">—</span>
                                    @endif
                                </td>
                                <td class="text-sm">{{ $studioClient->phone ?? '—' }}</td>
                                <td>
                                    @php
                                        $clientStatusColors = [
                                            'active' => 'badge-success',
                                            'lead' => 'badge-info',
                                            'inactive' => 'badge-neutral',
                                            'archived' => 'badge-neutral',
                                        ];
                                    @endphp
                                    <span class="badge badge-soft {{ $clientStatusColors[$studioClient->status] ?? 'badge-neutral' }} badge-sm capitalize">
                                        {{ $studioClient->status ?? 'unknown' }}
                                    </span>
                                </td>
                                <td>
                                    @php $membershipCount = $studioClient->customerMemberships()->count(); @endphp
                                    @if($membershipCount > 0)
                                        <span class="badge badge-soft badge-success badge-sm">{{ $membershipCount }}</span>
                                    @else
                                        <span class="text-base-content/40">—</span>
                                    @endif
                                </td>
                                <td>
                                    @php $bookingCount = $studioClient->bookings()->count(); @endphp
                                    @if($bookingCount > 0)
                                        <span class="badge badge-soft badge-primary badge-sm">{{ $bookingCount }}</span>
                                    @else
                                        <span class="text-base-content/40">—</span>
                                    @endif
                                </td>
                                <td class="text-sm text-base-content/70">{{ $studioClient->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($studioClients->hasPages())
                <div class="mt-4 flex justify-center">
                    {{ $studioClients->appends(['tab' => 'clients'])->links() }}
                </div>
                @endif
                @else
                <div class="text-center py-12 text-base-content/60">
                    <span class="icon-[tabler--users-group] size-12 opacity-30 mb-2"></span>
                    <p>No clients/members found for this studio</p>
                </div>
                @endif
            </div>

            {{-- Users Tab (Team Members) --}}
            <div id="tab-users" class="tab-content hidden">
                @if($client->users->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
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
                                        <div>
                                            <span class="font-medium">{{ $user->first_name }} {{ $user->last_name }}</span>
                                            @if($client->owner && $user->id === $client->owner->id)
                                                <span class="badge badge-soft badge-warning badge-xs ml-1">Owner</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="mailto:{{ $user->email }}" class="text-primary hover:underline">{{ $user->email }}</a>
                                </td>
                                <td>{{ $user->phone ?? '—' }}</td>
                                <td><span class="badge badge-soft badge-neutral badge-sm capitalize">{{ $user->role ?? 'member' }}</span></td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12 text-base-content/60">
                    <span class="icon-[tabler--users] size-12 opacity-30 mb-2"></span>
                    <p>No team members found</p>
                </div>
                @endif
            </div>

            {{-- Configuration Tab --}}
            <div id="tab-config" class="tab-content hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    {{-- Languages --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--language] size-5 text-primary"></span>
                            Languages
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">App Language</dt>
                                <dd class="font-medium uppercase">{{ $client->default_language_app ?? 'en' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Booking Language</dt>
                                <dd class="font-medium uppercase">{{ $client->default_language_booking ?? 'en' }}</dd>
                            </div>
                            <div class="py-2">
                                <dt class="text-base-content/60 mb-2">Studio Languages</dt>
                                <dd>
                                    @if($client->studio_languages && count($client->studio_languages) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($client->studio_languages as $lang)
                                                <span class="badge badge-soft badge-neutral badge-sm uppercase">{{ $lang }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-base-content/40">English only</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Currencies --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--currency-dollar] size-5 text-primary"></span>
                            Currencies
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Default Currency</dt>
                                <dd class="font-medium uppercase">{{ $client->default_currency ?? 'USD' }}</dd>
                            </div>
                            <div class="py-2">
                                <dt class="text-base-content/60 mb-2">Accepted Currencies</dt>
                                <dd>
                                    @if($client->currencies && count($client->currencies) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($client->currencies as $currency)
                                                <span class="badge badge-soft badge-neutral badge-sm uppercase">{{ $currency }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-base-content/40">USD only</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="py-2">
                                <dt class="text-base-content/60 mb-2">Operating Countries</dt>
                                <dd>
                                    @if($client->operating_countries && count($client->operating_countries) > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($client->operating_countries as $country)
                                                <span class="badge badge-soft badge-neutral badge-sm uppercase">{{ $country }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-base-content/40">Not set</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Features --}}
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--settings] size-5 text-primary"></span>
                            Features
                        </h4>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between py-2 border-b border-base-content/5">
                                <span class="text-sm text-base-content/60">Member Portal</span>
                                @if($client->member_portal_settings && ($client->member_portal_settings['enabled'] ?? false))
                                    <span class="badge badge-success badge-sm">Enabled</span>
                                @else
                                    <span class="badge badge-neutral badge-sm">Disabled</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-base-content/5">
                                <span class="text-sm text-base-content/60">Stripe Connected</span>
                                @if($client->stripe_account_id)
                                    <span class="badge badge-success badge-sm">Yes</span>
                                @else
                                    <span class="badge badge-neutral badge-sm">No</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-base-content/5">
                                <span class="text-sm text-base-content/60">Tax Settings</span>
                                @if($client->tax_settings && ($client->tax_settings['tax_enabled'] ?? false))
                                    <span class="badge badge-success badge-sm">Configured</span>
                                @else
                                    <span class="badge badge-neutral badge-sm">Not set</span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between py-2 border-b border-base-content/5">
                                <span class="text-sm text-base-content/60">Booking Page</span>
                                <span class="badge badge-soft badge-sm capitalize">{{ $client->booking_page_status ?? 'draft' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Raw Settings (Collapsible) --}}
                <details class="mt-8 collapse collapse-arrow bg-base-200/50 rounded-lg">
                    <summary class="collapse-title text-sm font-medium">
                        <span class="icon-[tabler--code] size-4 mr-1"></span>
                        Raw Settings (JSON)
                    </summary>
                    <div class="collapse-content space-y-4 pt-4">
                        @if($client->booking_settings)
                        <div>
                            <div class="text-xs text-base-content/60 mb-1">Booking Settings</div>
                            <pre class="text-xs bg-base-300 p-3 rounded-lg overflow-x-auto">{{ json_encode($client->booking_settings, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                        @if($client->payment_settings)
                        <div>
                            <div class="text-xs text-base-content/60 mb-1">Payment Settings</div>
                            <pre class="text-xs bg-base-300 p-3 rounded-lg overflow-x-auto">{{ json_encode($client->payment_settings, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                        @if($client->client_settings)
                        <div>
                            <div class="text-xs text-base-content/60 mb-1">Client Settings</div>
                            <pre class="text-xs bg-base-300 p-3 rounded-lg overflow-x-auto">{{ json_encode($client->client_settings, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                        @if($client->policies)
                        <div>
                            <div class="text-xs text-base-content/60 mb-1">Policies</div>
                            <pre class="text-xs bg-base-300 p-3 rounded-lg overflow-x-auto">{{ json_encode($client->policies, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @endif
                    </div>
                </details>
            </div>

            {{-- Subscription Tab --}}
            <div id="tab-subscription" class="tab-content hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--credit-card] size-5 text-primary"></span>
                            Subscription Details
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Plan</dt>
                                <dd class="font-medium">{{ $client->plan?->name ?? 'No plan' }}</dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Status</dt>
                                <dd>
                                    @php
                                        $subColors = [
                                            'trialing' => 'badge-info',
                                            'active' => 'badge-success',
                                            'past_due' => 'badge-warning',
                                            'canceled' => 'badge-error',
                                        ];
                                    @endphp
                                    <span class="badge badge-soft {{ $subColors[$client->subscription_status] ?? 'badge-neutral' }} badge-sm capitalize">
                                        {{ $client->subscription_status ?? 'none' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Trial Ends</dt>
                                <dd class="font-medium">
                                    @if($client->trial_ends_at)
                                        {{ $client->trial_ends_at->format('M d, Y') }}
                                        @if($client->trial_ends_at->isFuture())
                                            <span class="text-xs text-base-content/60">({{ $client->trial_ends_at->diffForHumans() }})</span>
                                        @else
                                            <span class="text-xs text-error">(Expired)</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </div>
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Subscription Ends</dt>
                                <dd class="font-medium">
                                    @if($client->subscription_ends_at)
                                        {{ $client->subscription_ends_at->format('M d, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
                            <span class="icon-[tabler--brand-stripe] size-5 text-primary"></span>
                            Payment Integration
                        </h4>
                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-base-content/5">
                                <dt class="text-base-content/60">Stripe Account</dt>
                                <dd class="font-medium font-mono text-xs">
                                    @if($client->stripe_account_id)
                                        {{ $client->stripe_account_id }}
                                    @else
                                        <span class="text-base-content/40">Not connected</span>
                                    @endif
                                </dd>
                            </div>
                            @if($client->payment_settings)
                                <div class="flex justify-between py-2 border-b border-base-content/5">
                                    <dt class="text-base-content/60">Accept Cards</dt>
                                    <dd>
                                        @if($client->payment_settings['accept_cards'] ?? false)
                                            <span class="badge badge-success badge-xs">Yes</span>
                                        @else
                                            <span class="badge badge-neutral badge-xs">No</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between py-2 border-b border-base-content/5">
                                    <dt class="text-base-content/60">Accept Cash</dt>
                                    <dd>
                                        @if($client->payment_settings['accept_cash'] ?? false)
                                            <span class="badge badge-success badge-xs">Yes</span>
                                        @else
                                            <span class="badge badge-neutral badge-xs">No</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
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
                <div class="text-center py-12 text-base-content/60">
                    <span class="icon-[tabler--history] size-12 opacity-30 mb-2"></span>
                    <p>No status changes recorded</p>
                </div>
                @endif
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

    function activateTab(tabName) {
        var targetId = 'tab-' + tabName;
        var targetTab = document.querySelector('[data-tab="' + tabName + '"]');

        if (!targetTab || !document.getElementById(targetId)) return;

        // Update tab states
        tabs.forEach(function(t) { t.classList.remove('tab-active'); });
        targetTab.classList.add('tab-active');

        // Update content visibility
        document.querySelectorAll('.tab-content').forEach(function(content) {
            content.classList.add('hidden');
        });
        document.getElementById(targetId).classList.remove('hidden');
    }

    // Check URL for tab parameter
    var urlParams = new URLSearchParams(window.location.search);
    var tabParam = urlParams.get('tab');
    if (tabParam) {
        activateTab(tabParam);
    }

    // Tab click handlers
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            activateTab(this.dataset.tab);

            // Update URL without reload
            var url = new URL(window.location);
            url.searchParams.set('tab', this.dataset.tab);
            window.history.replaceState({}, '', url);
        });
    });
});
</script>
@endsection
