@extends('layouts.dashboard')

@section('title', $trans['nav.instructors'] ?? 'Instructors')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> {{ $trans['nav.dashboard'] ?? 'Dashboard' }}</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--user-star] me-1 size-4"></span> {{ $trans['nav.instructors'] ?? 'Instructors' }}</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">{{ $trans['nav.instructors'] ?? 'Instructors' }}</h1>
        <p class="text-base-content/60 mt-1">{{ $trans['instructors.manage_description'] ?? 'Manage your studio\'s instructors, view schedules, and track assignments.' }}</p>
    </div>

    {{-- Actions Row --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div></div>
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('instructors.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                   class="btn btn-sm {{ $view === 'list' ? 'btn-active' : 'btn-ghost' }}" title="{{ $trans['common.list'] ?? 'List View' }}">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('instructors.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                   class="btn btn-sm {{ $view === 'card' ? 'btn-active' : 'btn-ghost' }}" title="{{ $trans['common.grid'] ?? 'Card View' }}">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('instructors.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="search">{{ $trans['btn.search'] ?? 'Search' }}</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               placeholder="{{ $trans['instructors.search_placeholder'] ?? 'Name or email...' }}"
                               class="input w-full pl-10">
                    </div>
                </div>
                <div class="w-40">
                    <label class="label-text" for="status">{{ $trans['common.status'] ?? 'Status' }}</label>
                    <select id="status" name="status" class="select w-full">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['common.statuses'] ?? 'Statuses' }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ $trans['common.active'] ?? 'Active' }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ $trans['common.inactive'] ?? 'Inactive' }}</option>
                    </select>
                </div>
                <div class="w-44">
                    <label class="label-text" for="employment_type">{{ $trans['instructors.employment'] ?? 'Employment' }}</label>
                    <select id="employment_type" name="employment_type" class="select w-full">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['common.types'] ?? 'Types' }}</option>
                        @foreach($employmentTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('employment_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="rate_type">{{ $trans['instructors.rate_type'] ?? 'Rate Type' }}</label>
                    <select id="rate_type" name="rate_type" class="select w-full">
                        <option value="">{{ $trans['common.all'] ?? 'All' }} {{ $trans['instructors.rates'] ?? 'Rates' }}</option>
                        @foreach($rateTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('rate_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--filter] size-5"></span>
                        {{ $trans['btn.filter'] ?? 'Filter' }}
                    </button>
                    @if(request()->hasAny(['search', 'status', 'employment_type', 'rate_type']))
                        <a href="{{ route('instructors.index', ['view' => $view]) }}" class="btn btn-ghost">
                            <span class="icon-[tabler--x] size-5"></span>
                            {{ $trans['btn.clear'] ?? 'Clear' }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Content: List or Card View --}}
    @if($instructors->isEmpty())
        <div class="card bg-base-100">
            <div class="card-body text-center py-12">
                <span class="icon-[tabler--user-star] size-16 text-base-content/20 mx-auto mb-4"></span>
                <h3 class="text-lg font-semibold mb-2">{{ $trans['instructors.no_instructors'] ?? 'No Instructors Found' }}</h3>
                <p class="text-base-content/60 mb-4">{{ $trans['instructors.get_started'] ?? 'Get started by adding your first instructor.' }}</p>
                <a href="{{ route('settings.team.users.invite') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    {{ $trans['instructors.add_instructor'] ?? 'Add Instructor' }}
                </a>
            </div>
        </div>
    @elseif($view === 'card')
        {{-- Card View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($instructors as $instructor)
                <div class="card bg-base-100 hover:shadow-lg transition-shadow">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-4">
                            {{-- Avatar --}}
                            @if($instructor->photo_url)
                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                     class="w-14 h-14 rounded-full object-cover">
                            @else
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content w-14 h-14 rounded-full font-bold text-lg">
                                        {{ $instructor->initials }}
                                    </div>
                                </div>
                            @endif

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <a href="{{ route('instructors.show', $instructor) }}" class="font-semibold hover:text-primary truncate">
                                        {{ $instructor->name }}
                                    </a>
                                    @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                                        <span class="badge badge-soft badge-sm badge-warning" title="{{ $trans['instructors.profile_incomplete'] ?? 'Profile incomplete' }}">
                                            {{ $trans['common.pending'] ?? 'Pending' }}
                                        </span>
                                    @elseif($instructor->is_active)
                                        <span class="badge badge-soft badge-sm badge-success">{{ $trans['common.active'] ?? 'Active' }}</span>
                                    @else
                                        <span class="badge badge-soft badge-sm badge-neutral">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                                    @endif
                                </div>
                                @if($instructor->email)
                                    <p class="text-sm text-base-content/60 truncate">{{ $instructor->email }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Specialties --}}
                        @if($instructor->specialties && count($instructor->specialties) > 0)
                            <div class="flex flex-wrap gap-1 mt-3">
                                @foreach(array_slice($instructor->specialties, 0, 3) as $specialty)
                                    <span class="badge badge-soft badge-primary badge-xs">{{ $specialty }}</span>
                                @endforeach
                                @if(count($instructor->specialties) > 3)
                                    <span class="badge badge-soft badge-neutral badge-xs">+{{ count($instructor->specialties) - 3 }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="divider my-2"></div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between text-sm">
                            <div class="space-y-1">
                                @if($instructor->getFormattedEmploymentType())
                                    <p class="text-base-content/60">{{ $instructor->getFormattedEmploymentType() }}</p>
                                @endif
                                @if($instructor->getFormattedRate())
                                    <p class="font-medium text-success">{{ $instructor->getFormattedRate() }}</p>
                                @endif
                            </div>

                            {{-- Actions --}}
                            <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['instructors.view_profile'] ?? 'View Profile' }}">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- List View --}}
        <div class="card bg-base-100">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ $trans['field.instructor'] ?? 'Instructor' }}</th>
                                <th>{{ $trans['common.status'] ?? 'Status' }}</th>
                                <th>{{ $trans['instructors.total_sessions'] ?? 'Total Sessions' }}</th>
                                <th class="w-20">{{ $trans['common.actions'] ?? 'Actions' }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instructors as $instructor)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @php
                                                $photoSrc = $instructor->photo_url ?? $instructor->user?->profile_photo_url;
                                            @endphp
                                            @if($photoSrc)
                                                <img src="{{ $photoSrc }}" alt="{{ $instructor->name }}"
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-sm">
                                                    {{ $instructor->initials }}
                                                </div>
                                            @endif
                                            <div>
                                                <a href="{{ route('instructors.show', $instructor) }}" class="font-medium hover:text-primary">
                                                    {{ $instructor->name }}
                                                </a>
                                                <div class="text-xs text-base-content/50">{{ $instructor->email ?? $instructor->phone ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $hasAccount = $instructor->hasAccount();
                                            $linkedUser = $instructor->user;
                                            if (!$hasAccount && $instructor->email) {
                                                $linkedUser = \App\Models\User::where('email', $instructor->email)
                                                    ->whereNotNull('password')
                                                    ->first();
                                                $hasAccount = $linkedUser !== null;
                                            }
                                        @endphp
                                        <div class="flex flex-wrap items-center gap-1">
                                            @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                                                <span class="badge badge-soft badge-sm badge-warning">{{ $trans['common.pending'] ?? 'Pending' }}</span>
                                            @elseif($instructor->is_active)
                                                <span class="badge badge-soft badge-sm badge-success">{{ $trans['common.active'] ?? 'Active' }}</span>
                                            @else
                                                <span class="badge badge-soft badge-sm badge-neutral">{{ $trans['common.inactive'] ?? 'Inactive' }}</span>
                                            @endif
                                            @if($hasAccount)
                                                <span class="badge badge-soft badge-success badge-xs">{{ $trans['instructors.granted'] ?? 'Access' }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-medium">{{ $instructor->primary_sessions_count }}</span>
                                        <span class="text-xs text-base-content/50">{{ Str::plural('session', $instructor->primary_sessions_count) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-xs btn-square" title="{{ $trans['instructors.view_profile'] ?? 'View Profile' }}">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- Pagination --}}
    @if($instructors->hasPages())
        <div class="flex justify-center">
            {{ $instructors->links() }}
        </div>
    @endif
</div>

@endsection
