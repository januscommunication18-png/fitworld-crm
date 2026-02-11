@extends('layouts.dashboard')

@section('title', 'Client Tags')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('clients.index') }}">Clients</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page"><span class="icon-[tabler--tags] me-1 size-4"></span> Tags</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Client Tags</h1>
            <p class="text-base-content/60 mt-1">Organize and segment your clients with custom tags.</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="document.getElementById('create-tag-modal').showModal()">
            <span class="icon-[tabler--plus] size-5"></span>
            Create Tag
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--tags] size-6 text-primary"></span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold">{{ $tags->total() }}</p>
                        <p class="text-xs text-base-content/60">Total Tags</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--users] size-6 text-success"></span>
                    </div>
                    <div>
                        @php
                            $totalUsage = $tags->sum('usage_count');
                        @endphp
                        <p class="text-2xl font-bold">{{ $totalUsage }}</p>
                        <p class="text-xs text-base-content/60">Total Assignments</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--star] size-6 text-warning"></span>
                    </div>
                    <div>
                        @php
                            $mostUsed = $tags->sortByDesc('usage_count')->first();
                        @endphp
                        <p class="text-2xl font-bold truncate max-w-[100px]">{{ $mostUsed?->name ?? '-' }}</p>
                        <p class="text-xs text-base-content/60">Most Used Tag</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-base-100">
            <div class="card-body p-4">
                <div class="flex items-center gap-3">
                    <div class="bg-info/10 rounded-lg p-2">
                        <span class="icon-[tabler--calendar-plus] size-6 text-info"></span>
                    </div>
                    <div>
                        @php
                            $recentTag = $tags->sortByDesc('created_at')->first();
                        @endphp
                        <p class="text-2xl font-bold truncate max-w-[100px]">{{ $recentTag?->name ?? '-' }}</p>
                        <p class="text-xs text-base-content/60">Latest Tag</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tags Grid --}}
    <div class="card bg-base-100">
        <div class="card-body">
            @if($tags->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($tags as $tag)
                        <div class="flex items-center justify-between p-4 rounded-xl border border-base-200 hover:border-base-300 hover:shadow-md transition-all bg-base-50">
                            <div class="flex items-center gap-3">
                                <span class="w-5 h-5 rounded-full shrink-0 ring-2 ring-offset-2 ring-base-200" style="background-color: {{ $tag->color }};"></span>
                                <div>
                                    <p class="font-semibold">{{ $tag->name }}</p>
                                    <p class="text-sm text-base-content/50">
                                        <span class="icon-[tabler--users] size-3.5 inline mr-0.5"></span>
                                        {{ $tag->usage_count }} client{{ $tag->usage_count !== 1 ? 's' : '' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex gap-1">
                                <button type="button" class="btn btn-ghost btn-sm btn-square"
                                        onclick="openEditTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}')"
                                        title="Edit">
                                    <span class="icon-[tabler--edit] size-4"></span>
                                </button>
                                <form method="POST" action="{{ route('clients.tags.destroy', $tag) }}"
                                      onsubmit="return confirm('Are you sure you want to delete this tag? It will be removed from all clients.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm btn-square text-error" title="Delete">
                                        <span class="icon-[tabler--trash] size-4"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($tags->hasPages())
                    <div class="mt-6 flex justify-center">{{ $tags->links() }}</div>
                @endif
            @else
                <div class="p-12 text-center">
                    <span class="icon-[tabler--tags] size-16 text-base-content/20 mx-auto mb-4"></span>
                    <h3 class="text-lg font-semibold mb-2">No Tags Yet</h3>
                    <p class="text-base-content/60 mb-4">Create tags to organize and segment your clients.</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('create-tag-modal').showModal()">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Create First Tag
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h3 class="font-semibold text-lg mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('clients.index') }}" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-primary hover:bg-primary/5 transition-colors">
                    <div class="bg-primary/10 rounded-lg p-2">
                        <span class="icon-[tabler--users] size-5 text-primary"></span>
                    </div>
                    <div>
                        <p class="font-medium">View All Clients</p>
                        <p class="text-sm text-base-content/60">Filter clients by tag</p>
                    </div>
                </a>
                <a href="{{ route('clients.leads') }}" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-warning hover:bg-warning/5 transition-colors">
                    <div class="bg-warning/10 rounded-lg p-2">
                        <span class="icon-[tabler--target] size-5 text-warning"></span>
                    </div>
                    <div>
                        <p class="font-medium">Tag Leads</p>
                        <p class="text-sm text-base-content/60">Organize your leads</p>
                    </div>
                </a>
                <a href="{{ route('clients.members') }}" class="flex items-center gap-3 p-4 rounded-lg border border-base-200 hover:border-success hover:bg-success/5 transition-colors">
                    <div class="bg-success/10 rounded-lg p-2">
                        <span class="icon-[tabler--user-check] size-5 text-success"></span>
                    </div>
                    <div>
                        <p class="font-medium">Tag Members</p>
                        <p class="text-sm text-base-content/60">Segment your members</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Create Tag Modal --}}
<dialog id="create-tag-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg flex items-center gap-2">
            <span class="icon-[tabler--tag] size-5 text-primary"></span>
            Create Tag
        </h3>
        <form method="POST" action="{{ route('clients.tags.store') }}" class="mt-4">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="create_name">Tag Name</label>
                    <input type="text" id="create_name" name="name" class="input input-bordered w-full" placeholder="e.g., VIP, Morning Class, Beginner" required>
                </div>
                <div>
                    <label class="label-text">Color</label>
                    <div class="flex flex-wrap gap-3 mt-2">
                        @foreach($defaultColors as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                                <span class="block w-10 h-10 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary hover:scale-110 transition-transform"
                                      style="background-color: {{ $color }};"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('create-tag-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-4"></span>
                    Create Tag
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Tag Modal --}}
<dialog id="edit-tag-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg flex items-center gap-2">
            <span class="icon-[tabler--edit] size-5 text-primary"></span>
            Edit Tag
        </h3>
        <form id="edit-tag-form" method="POST" class="mt-4">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="edit_name">Tag Name</label>
                    <input type="text" id="edit_name" name="name" class="input input-bordered w-full" required>
                </div>
                <div>
                    <label class="label-text">Color</label>
                    <div class="flex flex-wrap gap-3 mt-2" id="edit-color-options">
                        @foreach($defaultColors as $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color }}" class="peer hidden edit-color-radio">
                                <span class="block w-10 h-10 rounded-full peer-checked:ring-2 peer-checked:ring-offset-2 peer-checked:ring-primary hover:scale-110 transition-transform"
                                      style="background-color: {{ $color }};"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('edit-tag-modal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-4"></span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

@push('scripts')
<script>
function openEditTag(id, name, color) {
    const form = document.getElementById('edit-tag-form');
    form.action = '/clients/tags/' + id;
    document.getElementById('edit_name').value = name;

    // Select the correct color
    document.querySelectorAll('.edit-color-radio').forEach(radio => {
        radio.checked = (radio.value === color);
    });

    document.getElementById('edit-tag-modal').showModal();
}
</script>
@endpush
@endsection
