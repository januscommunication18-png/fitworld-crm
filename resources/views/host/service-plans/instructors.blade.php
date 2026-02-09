@extends('layouts.dashboard')

@section('title', 'Manage Instructors - ' . $servicePlan->name)

@section('breadcrumbs')
    <ol>
        <li><a href="{{ route('dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('catalog.index', ['tab' => 'services']) }}"><span class="icon-[tabler--layout-grid] me-1 size-4"></span> Catalog</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $servicePlan->name }} - Instructors</li>
    </ol>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Manage Instructors</h1>
            <p class="text-base-content/60 mt-1">Configure which instructors offer "{{ $servicePlan->name }}" and their pricing.</p>
        </div>
        <a href="{{ route('catalog.index', ['tab' => 'services']) }}" class="btn btn-ghost">
            <span class="icon-[tabler--arrow-left] size-5"></span>
            Back to Catalog
        </a>
    </div>

    {{-- Service Info --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center gap-4">
                @if($servicePlan->image_path)
                <img src="{{ $servicePlan->image_url }}" alt="{{ $servicePlan->name }}" class="w-16 h-16 rounded-lg object-cover">
                @else
                <div class="w-16 h-16 rounded-lg flex items-center justify-center" style="background-color: {{ $servicePlan->color }}20;">
                    <span class="icon-[tabler--user-check] size-8" style="color: {{ $servicePlan->color }};"></span>
                </div>
                @endif
                <div>
                    <h2 class="text-lg font-semibold">{{ $servicePlan->name }}</h2>
                    <p class="text-sm text-base-content/60">
                        {{ $servicePlan->formatted_duration }} &bull;
                        Base Price: {{ $servicePlan->formatted_price }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructors Form --}}
    <form action="{{ route('service-plans.instructors.update', $servicePlan) }}" method="POST">
        @csrf
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Assigned Instructors</h3>
                <span class="badge badge-neutral badge-sm">{{ $servicePlan->instructors->count() }} assigned</span>
            </div>
            <div class="card-body">
                @if($allInstructors->isEmpty())
                <p class="text-base-content/60">No active instructors available. <a href="{{ route('settings.team.instructors') }}" class="link link-primary">Add instructors</a> first.</p>
                @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Assigned</th>
                                <th>Instructor</th>
                                <th>Custom Price</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allInstructors as $instructor)
                            @php
                                $pivot = $servicePlan->instructors->firstWhere('id', $instructor->id)?->pivot;
                                $isAssigned = $pivot !== null;
                            @endphp
                            <tr>
                                <td>
                                    <input type="checkbox"
                                        class="checkbox checkbox-primary checkbox-sm instructor-checkbox"
                                        data-instructor-id="{{ $instructor->id }}"
                                        {{ $isAssigned ? 'checked' : '' }}
                                        onchange="toggleInstructorRow(this, {{ $instructor->id }})">
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        @if($instructor->photo_url)
                                        <img src="{{ $instructor->photo_url }}" alt="{{ $instructor->name }}" class="w-10 h-10 rounded-full object-cover">
                                        @else
                                        <div class="avatar avatar-placeholder">
                                            <div class="bg-primary text-primary-content w-10 h-10 rounded-full font-bold text-sm">
                                                {{ strtoupper(substr($instructor->name, 0, 2)) }}
                                            </div>
                                        </div>
                                        @endif
                                        <div>
                                            <div class="font-medium">{{ $instructor->name }}</div>
                                            @if($instructor->specialties)
                                            <div class="text-xs text-base-content/60">{{ implode(', ', array_slice($instructor->specialties, 0, 2)) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="instructor-fields-{{ $instructor->id }} {{ !$isAssigned ? 'hidden' : '' }}">
                                        <input type="hidden" name="instructors[{{ $instructor->id }}][id]" value="{{ $instructor->id }}" {{ !$isAssigned ? 'disabled' : '' }}>
                                        <input type="number"
                                            name="instructors[{{ $instructor->id }}][custom_price]"
                                            class="input input-sm w-28"
                                            placeholder="{{ $servicePlan->price ? number_format($servicePlan->price, 2) : 'Free' }}"
                                            value="{{ $pivot?->custom_price }}"
                                            min="0" max="9999.99" step="0.01"
                                            {{ !$isAssigned ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="instructor-fields-{{ $instructor->id }} {{ !$isAssigned ? 'hidden' : '' }}">
                                        <input type="checkbox"
                                            name="instructors[{{ $instructor->id }}][is_active]"
                                            class="toggle toggle-success toggle-sm"
                                            value="1"
                                            {{ $pivot?->is_active ?? true ? 'checked' : '' }}
                                            {{ !$isAssigned ? 'disabled' : '' }}>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--check] size-5"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function toggleInstructorRow(checkbox, instructorId) {
    var fields = document.querySelectorAll('.instructor-fields-' + instructorId);
    var inputs = document.querySelectorAll('.instructor-fields-' + instructorId + ' input');

    fields.forEach(function(el) {
        if (checkbox.checked) {
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });

    inputs.forEach(function(input) {
        input.disabled = !checkbox.checked;
    });
}
</script>
@endpush
@endsection
