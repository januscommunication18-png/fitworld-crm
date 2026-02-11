@extends('layouts.dashboard')

@section('title', 'Leads')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--target] me-1 size-4"></span> Leads</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold">Leads</h1>
        <p class="text-base-content/60 mt-1">Prospective clients captured from various sources.</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--target] size-6 text-warning"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $clients->total() }}</p>
                        <p class="text-xs text-base-content/60">Total Leads</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--calendar] size-6 text-info"></span>
                    </div>
                    <div>
                        @php
                            $hostId = auth()->user()->currentHost()->id ?? auth()->user()->host_id;
                            $thisWeekLeads = \App\Models\Client::forHost($hostId)->active()->leads()->where('created_at', '>=', now()->startOfWeek())->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $thisWeekLeads }}</p>
                        <p class="text-xs text-base-content/60">This Week</p>
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
                        @php
                            $convertedThisMonth = \App\Models\Client::forHost($hostId)->whereIn('status', ['client', 'member'])->where('converted_at', '>=', now()->startOfMonth())->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $convertedThisMonth }}</p>
                        <p class="text-xs text-base-content/60">Converted This Month</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--world] size-6 text-primary"></span>
                    </div>
                    <div>
                        @php
                            $webLeads = \App\Models\Client::forHost($hostId)->active()->leads()->where('lead_source', 'website')->count();
                        @endphp
                        <p class="text-2xl font-bold">{{ $webLeads }}</p>
                        <p class="text-xs text-base-content/60">From Website</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabs & Actions --}}
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="tabs tabs-bordered">
            <a href="{{ route('clients.index') }}" class="tab">
                <span class="icon-[tabler--users] size-4 mr-2"></span>
                All Clients
            </a>
            <a href="{{ route('clients.leads') }}" class="tab tab-active">
                <span class="icon-[tabler--target] size-4 mr-2"></span>
                Leads
            </a>
            <a href="{{ route('clients.members') }}" class="tab">
                <span class="icon-[tabler--user-check] size-4 mr-2"></span>
                Members
            </a>
            <a href="{{ route('clients.at-risk') }}" class="tab">
                <span class="icon-[tabler--alert-triangle] size-4 mr-2"></span>
                At-Risk
            </a>
        </div>

        <div class="flex items-center gap-2">
            {{-- View Toggle --}}
            <div class="btn-group">
                <a href="{{ route('clients.leads', array_merge(request()->query(), ['view' => 'list'])) }}"
                   class="btn btn-sm {{ request('view', 'list') === 'list' ? 'btn-active' : 'btn-ghost' }}" title="List View">
                    <span class="icon-[tabler--list] size-4"></span>
                </a>
                <a href="{{ route('clients.leads', array_merge(request()->query(), ['view' => 'grid'])) }}"
                   class="btn btn-sm {{ request('view') === 'grid' ? 'btn-active' : 'btn-ghost' }}" title="Grid View">
                    <span class="icon-[tabler--layout-grid] size-4"></span>
                </a>
            </div>

            <a href="{{ route('clients.create') }}?status=lead" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Lead
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('clients.leads') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <input type="hidden" name="view" value="{{ request('view', 'list') }}">
                <div class="flex-1 min-w-[200px]">
                    <label class="label-text" for="search">Search</label>
                    <div class="relative">
                        <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/50"></span>
                        <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Name, email, or phone..."
                               class="input w-full pl-10">
                    </div>
                </div>
                <div class="w-40">
                    <label class="label-text" for="source">Source</label>
                    <select id="source" name="source" class="select w-full">
                        <option value="">All Sources</option>
                        @foreach($sources as $key => $label)
                            <option value="{{ $key }}" {{ ($filters['source'] ?? '') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <label class="label-text" for="tag">Tag</label>
                    <select id="tag" name="tag" class="select w-full">
                        <option value="">All Tags</option>
                        @foreach($tags ?? [] as $tag)
                            <option value="{{ $tag->id }}" {{ ($filters['tag'] ?? '') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--filter] size-5"></span>
                    Filter
                </button>
                @if(!empty(array_filter($filters ?? [])))
                    <a href="{{ route('clients.leads') }}" class="btn btn-ghost">
                        <span class="icon-[tabler--x] size-4"></span>
                        Clear
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- Content --}}
    @if($clients->isEmpty())
    <div class="card bg-base-100">
        <div class="card-body text-center py-12">
            <span class="icon-[tabler--target] size-16 text-base-content/20 mx-auto mb-4"></span>
            <h3 class="text-lg font-semibold mb-2">No Leads Found</h3>
            <p class="text-base-content/60 mb-4">
                @if(!empty(array_filter($filters ?? [])))
                    No leads match your current filters. Try adjusting your search.
                @else
                    Leads will appear here when captured from your website or marketing campaigns.
                @endif
            </p>
            @if(empty(array_filter($filters ?? [])))
            <a href="{{ route('clients.create') }}?status=lead" class="btn btn-primary">
                <span class="icon-[tabler--plus] size-5"></span>
                Add Your First Lead
            </a>
            @else
            <a href="{{ route('clients.leads') }}" class="btn btn-ghost">Clear Filters</a>
            @endif
        </div>
    </div>
    @else
        @if(request('view') === 'grid')
        {{-- Grid View --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($clients as $client)
            <div class="card bg-base-100 hover:shadow-lg transition-shadow">
                <div class="card-body p-4">
                    <div class="flex items-start gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-warning text-warning-content size-14 rounded-full font-bold text-lg">
                                {{ $client->initials }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <a href="{{ route('clients.show', $client) }}" class="font-semibold hover:text-primary transition-colors">
                                        {{ $client->full_name }}
                                    </a>
                                    <p class="text-sm text-base-content/60 truncate">{{ $client->email }}</p>
                                </div>
                                <span class="badge badge-soft badge-warning badge-sm shrink-0">Lead</span>
                            </div>

                            @if($client->phone)
                            <p class="text-sm text-base-content/60 mt-1">
                                <span class="icon-[tabler--phone] size-3.5 inline"></span>
                                {{ $client->phone }}
                            </p>
                            @endif

                            @if($client->tags->count() > 0)
                            <div class="flex flex-wrap gap-1 mt-2">
                                @foreach($client->tags->take(3) as $tag)
                                    <span class="badge badge-xs" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                                @if($client->tags->count() > 3)
                                    <span class="badge badge-xs badge-ghost">+{{ $client->tags->count() - 3 }}</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-4 text-base-content/60">
                            <span title="Source">
                                <span class="icon-[tabler--source-code] size-4 inline"></span>
                                {{ $sources[$client->lead_source] ?? $client->lead_source }}
                            </span>
                            <span title="Captured">
                                <span class="icon-[tabler--calendar] size-4 inline"></span>
                                {{ $client->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <div class="flex gap-1">
                            <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                <span class="icon-[tabler--eye] size-4"></span>
                            </a>
                            <form method="POST" action="{{ route('clients.convert-to-client', $client) }}">
                                @csrf
                                <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Convert to Client">
                                    <span class="icon-[tabler--user-check] size-4"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        {{-- List View --}}
        <div class="card bg-base-100 overflow-visible">
            <div class="card-body p-0 overflow-visible">
                <div class="overflow-x-auto overflow-y-visible">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Lead</th>
                                <th>Source</th>
                                <th>Tags</th>
                                <th>Captured</th>
                                <th class="w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clients as $client)
                            <tr class="hover:bg-base-200/50">
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-warning/10 text-warning w-10 h-10 rounded-full">
                                                <span class="text-sm font-semibold">{{ $client->initials }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('clients.show', $client) }}" class="font-medium hover:text-primary">
                                                {{ $client->full_name }}
                                            </a>
                                            <div class="text-sm text-base-content/60">{{ $client->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $sources[$client->lead_source] ?? $client->lead_source }}</span>
                                    @if($client->source_url)
                                        <div class="text-xs text-base-content/50 truncate max-w-[150px]" title="{{ $client->source_url }}">
                                            {{ parse_url($client->source_url, PHP_URL_HOST) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($client->tags->take(2) as $tag)
                                            <span class="badge badge-sm" style="background-color: {{ $tag->color }}20; color: {{ $tag->color }};">{{ $tag->name }}</span>
                                        @endforeach
                                        @if($client->tags->count() > 2)
                                            <span class="badge badge-sm badge-ghost">+{{ $client->tags->count() - 2 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $client->created_at->diffForHumans() }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('clients.show', $client) }}" class="btn btn-ghost btn-xs btn-square" title="View">
                                            <span class="icon-[tabler--eye] size-4"></span>
                                        </a>
                                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </a>
                                        <form method="POST" action="{{ route('clients.convert-to-client', $client) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs btn-square text-success" title="Convert to Client">
                                                <span class="icon-[tabler--user-check] size-4"></span>
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
        @endif

        {{-- Pagination --}}
        @if($clients->hasPages())
        <div class="flex justify-center">
            {{ $clients->links() }}
        </div>
        @endif
    @endif
</div>
@endsection
