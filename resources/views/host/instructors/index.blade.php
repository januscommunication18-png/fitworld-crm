@extends('layouts.dashboard')

@section('title', 'Instructors')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--user-star] me-1 size-4"></span> Instructors</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Instructors</h1>
        <p class="text-base-content/60 mt-1">Manage your studio's instructors, view schedules, and track assignments.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--users] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
                        <p class="text-xs text-base-content/60">Total</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--user-check] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['active'] }}</p>
                        <p class="text-xs text-base-content/60">Active</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--key] size-6 text-success"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['with_login'] }}</p>
                        <p class="text-xs text-base-content/60">Access Granted</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-neutral/10 rounded-lg p-2">
                        <span class="icon-[tabler--key-off] size-6 text-neutral"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $stats['no_login'] }}</p>
                        <p class="text-xs text-base-content/60">No Access</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions Row --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div></div>
        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('instructors.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                   class="btn btn-sm {{ $view === 'list' ? 'btn-active' : 'btn-ghost' }}" title="List View">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('instructors.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                   class="btn btn-sm {{ $view === 'card' ? 'btn-active' : 'btn-ghost' }}" title="Card View">
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
                    <label class="label-text" for="search">Search</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               placeholder="Name or email..."
                               class="input w-full pl-10">
                    </div>
                </div>
                <div class="w-40">
                    <label class="label-text" for="status">Status</label>
                    <select id="status" name="status" class="select w-full">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="w-44">
                    <label class="label-text" for="employment_type">Employment</label>
                    <select id="employment_type" name="employment_type" class="select w-full">
                        <option value="">All Types</option>
                        @foreach($employmentTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('employment_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="rate_type">Rate Type</label>
                    <select id="rate_type" name="rate_type" class="select w-full">
                        <option value="">All Rates</option>
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
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'employment_type', 'rate_type']))
                        <a href="{{ route('instructors.index', ['view' => $view]) }}" class="btn btn-ghost">
                            <span class="icon-[tabler--x] size-5"></span>
                            Clear
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
                <h3 class="text-lg font-semibold mb-2">No Instructors Found</h3>
                <p class="text-base-content/60 mb-4">Get started by adding your first instructor.</p>
                <a href="{{ route('instructors.create') }}" class="btn btn-primary">
                    <span class="icon-[tabler--plus] size-5"></span>
                    Add Instructor
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
                                        <span class="badge badge-soft badge-sm badge-warning" title="Profile incomplete">
                                            Pending
                                        </span>
                                    @elseif($instructor->is_active)
                                        <span class="badge badge-soft badge-sm badge-success">Active</span>
                                    @else
                                        <span class="badge badge-soft badge-sm badge-neutral">Inactive</span>
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
                            <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
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
                                <th>Instructor</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Employment</th>
                                <th>Rate</th>
                                <th>Access Level</th>
                                <th class="w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instructors as $instructor)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @if($instructor->photo_url)
                                                <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}"
                                                     class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <div class="avatar placeholder">
                                                    <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                                        {{ $instructor->initials }}
                                                    </div>
                                                </div>
                                            @endif
                                            <a href="{{ route('instructors.show', $instructor) }}" class="font-medium hover:text-primary">
                                                {{ $instructor->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-base-content/70">{{ $instructor->email ?? '-' }}</td>
                                    <td class="text-base-content/70">{{ $instructor->phone ?? '-' }}</td>
                                    <td>
                                        @if($instructor->status === 'pending' || !$instructor->isProfileComplete())
                                            <span class="badge badge-soft badge-sm badge-warning" title="Profile incomplete">
                                                Pending
                                            </span>
                                        @elseif($instructor->is_active)
                                            <span class="badge badge-soft badge-sm badge-success">Active</span>
                                        @else
                                            <span class="badge badge-soft badge-sm badge-neutral">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-base-content/70">{{ $instructor->getFormattedEmploymentType() ?? '-' }}</td>
                                    <td class="text-success font-medium">{{ $instructor->getFormattedRate() ?? '-' }}</td>
                                    <td>
                                        @php
                                            $hasAccount = $instructor->hasAccount();
                                            $linkedUser = $instructor->user;

                                            // If no direct link, check if user exists with same email
                                            if (!$hasAccount && $instructor->email) {
                                                $linkedUser = \App\Models\User::where('email', $instructor->email)
                                                    ->whereNotNull('password')
                                                    ->first();
                                                $hasAccount = $linkedUser !== null;
                                            }
                                        @endphp
                                        @if($hasAccount)
                                            <span class="badge badge-soft badge-success badge-sm">Granted</span>
                                            @if($linkedUser && $linkedUser->status === 'invited')
                                                <span class="badge badge-soft badge-warning badge-xs ml-1">Invite Pending</span>
                                            @endif
                                        @else
                                            <span class="badge badge-soft badge-neutral badge-sm">No Access</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('instructors.show', $instructor) }}" class="btn btn-ghost btn-xs btn-square" title="View Profile">
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
