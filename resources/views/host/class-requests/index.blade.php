@extends('layouts.dashboard')

@section('title', 'Class Requests')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--message-circle-question] me-1 size-4"></span> Requests</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Class Requests</h1>
            <p class="text-base-content/60 mt-1">Review and manage class requests from clients.</p>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="flex items-center gap-2 flex-wrap border-b border-base-content/10">
        <a href="{{ route('class-requests.index') }}" class="px-4 py-2 border-b-2 {{ !$currentStatus ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            All <span class="badge badge-sm ml-1">{{ array_sum($statusCounts) }}</span>
        </a>
        <a href="{{ route('class-requests.index', ['status' => 'open']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'open' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Open <span class="badge badge-info badge-sm ml-1">{{ $statusCounts['open'] }}</span>
        </a>
        <a href="{{ route('class-requests.index', ['status' => 'in_discussion']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'in_discussion' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            In Discussion <span class="badge badge-warning badge-sm ml-1">{{ $statusCounts['in_discussion'] }}</span>
        </a>
        <a href="{{ route('class-requests.index', ['status' => 'need_to_convert']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'need_to_convert' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Need to Convert <span class="badge badge-primary badge-sm ml-1">{{ $statusCounts['need_to_convert'] }}</span>
        </a>
        <a href="{{ route('class-requests.index', ['status' => 'booked']) }}" class="px-4 py-2 border-b-2 {{ $currentStatus === 'booked' ? 'border-primary text-primary font-medium' : 'border-transparent text-base-content/60 hover:text-base-content' }}">
            Booked <span class="badge badge-success badge-sm ml-1">{{ $statusCounts['booked'] }}</span>
        </a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('class-requests.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <div class="flex-1 min-w-[150px]">
                    <label class="label-text" for="type">Type</label>
                    <select id="type" name="type" class="select w-full">
                        <option value="">All Types</option>
                        <option value="class" {{ $currentType === 'class' ? 'selected' : '' }}>Classes</option>
                        <option value="service" {{ $currentType === 'service' ? 'selected' : '' }}>Services</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="class_plan_id">Class</label>
                    <select id="class_plan_id" name="class_plan_id" class="select w-full">
                        <option value="">All Classes</option>
                        @foreach($classPlans as $plan)
                        <option value="{{ $plan->id }}" {{ request('class_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="service_plan_id">Service</label>
                    <select id="service_plan_id" name="service_plan_id" class="select w-full">
                        <option value="">All Services</option>
                        @foreach($servicePlans as $plan)
                        <option value="{{ $plan->id }}" {{ request('service_plan_id') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-5"></span>
                    Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Requests List --}}
    @if($requests->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--inbox] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Requests Found</h3>
            <p class="text-base-content/60 mb-4">There are no class requests matching your filters.</p>
        </div>
    </div>
    @else
    <div class="card bg-base-100">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requester</th>
                            <th>Class</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Waitlist</th>
                            <th>Submitted</th>
                            <th class="w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $classRequest)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $classRequest->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ $classRequest->email }}</div>
                                @if($classRequest->phone)
                                <div class="text-sm text-base-content/60">{{ $classRequest->phone }}</div>
                                @endif
                            </td>
                            <td>
                                @if($classRequest->classPlan)
                                <div class="flex items-center gap-2">
                                    @if($classRequest->classPlan->color)
                                    <div class="w-3 h-3 rounded-full" style="background-color: {{ $classRequest->classPlan->color }};"></div>
                                    @endif
                                    {{ $classRequest->classPlan->name }}
                                </div>
                                @else
                                <span class="text-base-content/60">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="max-w-xs truncate text-sm text-base-content/70">
                                    {{ $classRequest->message ?: '-' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $classRequest->getStatusBadgeClass() }} badge-sm">
                                    {{ $statuses[$classRequest->status] ?? ucfirst(str_replace('_', ' ', $classRequest->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($classRequest->waitlist_requested)
                                <span class="badge badge-info badge-sm">Yes</span>
                                @else
                                <span class="text-base-content/40">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-sm">{{ $classRequest->created_at->format('M j, Y') }}</div>
                                <div class="text-xs text-base-content/60">{{ $classRequest->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('class-requests.show', $classRequest) }}" class="btn btn-ghost btn-xs btn-square" title="View Details">
                                        <span class="icon-[tabler--eye] size-4"></span>
                                    </a>
                                    @if($classRequest->helpdeskTicket)
                                    <a href="{{ route('helpdesk.show', $classRequest->helpdeskTicket) }}" class="btn btn-ghost btn-xs btn-square" title="View Ticket">
                                        <span class="icon-[tabler--ticket] size-4"></span>
                                    </a>
                                    @endif
                                    @if($classRequest->status !== 'booked')
                                    <form action="{{ route('class-requests.mark-booked', $classRequest) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Mark as Booked">
                                            <span class="icon-[tabler--check] size-4"></span>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('class-requests.destroy', $classRequest) }}" method="POST" class="inline" onsubmit="return confirm('Delete this request?')">
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
    @if($requests->hasPages())
    <div class="flex justify-center">
        {{ $requests->links() }}
    </div>
    @endif
    @endif
</div>
@endsection
