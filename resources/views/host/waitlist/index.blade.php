@extends('layouts.dashboard')

@section('title', 'Waitlist')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--hourglass] me-1 size-4"></span> Waitlist</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Waitlist</h1>
            <p class="text-base-content/60 mt-1">Manage clients waiting for spots in classes.</p>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="flex items-center gap-2 flex-wrap border-b border-base-content/10">
        <a href="{{ route('waitlist.index') }}" class="px-4 py-2 border-b-2 {{ !$currentStatus ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            All <span class="badge badge-sm ml-1">{{ array_sum($statusCounts) }}</span>
        </a>
        <a href="{{ route('waitlist.index', ['status' => 'waiting']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'waiting' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Waiting <span class="badge badge-info badge-sm ml-1">{{ $statusCounts['waiting'] }}</span>
        </a>
        <a href="{{ route('waitlist.index', ['status' => 'offered']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'offered' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Offered <span class="badge badge-warning badge-sm ml-1">{{ $statusCounts['offered'] }}</span>
        </a>
        <a href="{{ route('waitlist.index', ['status' => 'claimed']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'claimed' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Claimed <span class="badge badge-success badge-sm ml-1">{{ $statusCounts['claimed'] }}</span>
        </a>
        <a href="{{ route('waitlist.index', ['status' => 'expired']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'expired' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Expired <span class="badge badge-neutral badge-sm ml-1">{{ $statusCounts['expired'] }}</span>
        </a>
        <a href="{{ route('waitlist.index', ['status' => 'cancelled']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'cancelled' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Cancelled <span class="badge badge-error badge-sm ml-1">{{ $statusCounts['cancelled'] }}</span>
        </a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('waitlist.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="search">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="input input-bordered w-full" placeholder="Name, email, phone...">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="class_plan_id">Class</label>
                    <select id="class_plan_id" name="class_plan_id" class="select select-bordered w-full">
                        <option value="">All Classes</option>
                        @foreach($classPlans as $plan)
                        <option value="{{ $plan->id }}" {{ $currentClassPlan == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-5"></span>
                    Filter
                </button>
                @if($search || $currentClassPlan)
                <a href="{{ route('waitlist.index', $currentStatus ? ['status' => $currentStatus] : []) }}" class="btn btn-ghost">
                    Clear
                </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Waitlist Entries --}}
    @if($entries->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--hourglass-off] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Waitlist Entries</h3>
            <p class="text-base-content/60 mb-4">There are no waitlist entries matching your filters.</p>
        </div>
    </div>
    @else
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Person</th>
                            <th>Class</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Added</th>
                            <th class="w-40">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $entry->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ $entry->email }}</div>
                                @if($entry->phone)
                                <div class="text-sm text-base-content/60">{{ $entry->phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($entry->classPlan)
                                <div class="flex items-center gap-2">
                                    @if($entry->classPlan->color)
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $entry->classPlan->color }};"></div>
                                    @endif
                                    {{ $entry->classPlan->name }}
                                </div>
                                @else
                                <span class="text-base-content/60">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $entry->getStatusBadgeClass() }} badge-sm">
                                    {{ $statuses[$entry->status] ?? ucfirst($entry->status) }}
                                </span>
                                @if($entry->status === 'offered' && $entry->expires_at)
                                <div class="text-xs text-base-content/50 mt-1">
                                    Expires: {{ $entry->expires_at->diffForHumans() }}
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="max-w-xs truncate text-sm text-base-content/70">
                                    {{ $entry->notes ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-sm">{{ $entry->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $entry->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    @if($entry->classRequest)
                                    <a href="{{ route('class-requests.show', $entry->classRequest) }}" class="btn btn-ghost btn-xs btn-square" title="View Request">
                                        <span class="icon-[tabler--message-circle-question] size-4"></span>
                                    </a>
                                    @endif
                                    @if($entry->isWaiting())
                                    <form action="{{ route('waitlist.offer', $entry) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-warning" title="Offer Spot">
                                            <span class="icon-[tabler--bell] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                    @if($entry->isWaiting() || $entry->isOffered())
                                    <form action="{{ route('waitlist.cancel', $entry) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-base-content/50" title="Cancel">
                                            <span class="icon-[tabler--x] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('waitlist.destroy', $entry) }}" method="POST" class="inline" onsubmit="return confirm('Delete this entry?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    @if($entries->hasPages())
    <div class="flex justify-center">
        {{ $entries->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
