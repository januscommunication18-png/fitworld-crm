{{-- Schedule Filters Partial --}}
<div class="flex flex-wrap items-center gap-3">
    {{-- Type Filter --}}
    <div class="form-control w-auto">
        <select name="type" class="select select-bordered select-sm" onchange="applyFilters()">
            <option value="both" {{ ($filters['type'] ?? 'both') === 'both' ? 'selected' : '' }}>All Types</option>
            <option value="classes" {{ ($filters['type'] ?? '') === 'classes' ? 'selected' : '' }}>Classes Only</option>
            <option value="services" {{ ($filters['type'] ?? '') === 'services' ? 'selected' : '' }}>Services Only</option>
        </select>
    </div>

    {{-- Location Filter --}}
    @if($locations->count() > 1)
        <div class="form-control w-auto">
            <select name="location_id" class="select select-bordered select-sm" onchange="applyFilters()">
                <option value="">All Locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location->id }}" {{ ($filters['location_id'] ?? '') == $location->id ? 'selected' : '' }}>
                        {{ $location->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Instructor Filter --}}
    @if($instructors->count() > 0)
        <div class="form-control w-auto">
            <select name="instructor_id" class="select select-bordered select-sm" onchange="applyFilters()">
                <option value="">All Instructors</option>
                @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}" {{ ($filters['instructor_id'] ?? '') == $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->name }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Status Filter --}}
    <div class="form-control w-auto">
        <select name="status" class="select select-bordered select-sm" onchange="applyFilters()">
            <option value="active" {{ ($filters['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="" {{ ($filters['status'] ?? '') === '' ? 'selected' : '' }}>All Statuses</option>
            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft Only</option>
            <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Published Only</option>
            <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>

    {{-- Clear Filters --}}
    @if(($filters['type'] ?? 'both') !== 'both' || !empty($filters['location_id']) || !empty($filters['instructor_id']) || ($filters['status'] ?? 'active') !== 'active')
        <button type="button" class="btn btn-ghost btn-sm" onclick="clearFilters()">
            <span class="icon-[tabler--x] size-4"></span>
            Clear
        </button>
    @endif
</div>
