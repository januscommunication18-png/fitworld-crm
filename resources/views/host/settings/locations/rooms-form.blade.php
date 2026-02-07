@extends('layouts.settings')

@section('title', ($isEdit ? 'Edit' : 'Add') . ' Room — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.locations.rooms') }}">Rooms</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">{{ $isEdit ? 'Edit' : 'Add' }} Room</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">{{ $isEdit ? 'Edit Room' : 'Add Room' }}</h1>
            <p class="text-base-content/60 text-sm">{{ $isEdit ? 'Update room details' : 'Add a new room to your studio' }}</p>
        </div>
        <a href="{{ route('settings.locations.rooms') }}" class="btn btn-ghost btn-sm">
            <span class="icon-[tabler--arrow-left] size-4"></span> Back to Rooms
        </a>
    </div>

    {{-- Form Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form
                method="POST"
                action="{{ $isEdit ? route('settings.rooms.update', $room) : route('settings.rooms.store') }}"
            >
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="space-y-6">
                    {{-- Location Select --}}
                    <div>
                        <label class="label-text" for="location_id">Location <span class="text-error">*</span></label>
                        <select
                            id="location_id"
                            name="location_id"
                            data-select='{
                                "hasSearch": true,
                                "searchPlaceholder": "Search locations...",
                                "placeholder": "Select location...",
                                "toggleTag": "<button type=\"button\" aria-expanded=\"false\"></button>",
                                "toggleClasses": "advance-select-toggle w-full",
                                "dropdownClasses": "advance-select-menu max-h-48 overflow-y-auto",
                                "optionClasses": "advance-select-option selected:select-active",
                                "optionTemplate": "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                                "extraMarkup": "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content absolute top-1/2 end-3 -translate-y-1/2\"></span>"
                            }'
                            class="hidden"
                            required
                        >
                            <option value="">Select location...</option>
                            @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ old('location_id', $room->location_id ?? request('location')) == $location->id ? 'selected' : '' }}>
                                {{ $location->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('location_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Room Name --}}
                    <div>
                        <label class="label-text" for="name">Room Name <span class="text-error">*</span></label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            class="input w-full @error('name') input-error @enderror"
                            placeholder="e.g. Main Studio, Private Room, Studio A"
                            value="{{ old('name', $room->name ?? '') }}"
                            required
                        />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Capacity --}}
                    <div>
                        <label class="label-text" for="capacity">Capacity <span class="text-error">*</span></label>
                        <input
                            id="capacity"
                            name="capacity"
                            type="number"
                            min="1"
                            max="500"
                            class="input w-full @error('capacity') input-error @enderror"
                            placeholder="Maximum number of participants"
                            value="{{ old('capacity', $room->capacity ?? 10) }}"
                            required
                        />
                        <p class="text-base-content/60 text-sm mt-1">Maximum number of participants allowed in this room</p>
                        @error('capacity')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="label-text" for="description">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            class="textarea w-full"
                            rows="3"
                            placeholder="Brief description of the room (optional)"
                        >{{ old('description', $room->description ?? '') }}</textarea>
                    </div>

                    {{-- Room Dimensions --}}
                    <div>
                        <label class="label-text" for="dimensions">Room Dimensions</label>
                        <input
                            id="dimensions"
                            name="dimensions"
                            type="text"
                            class="input w-full"
                            placeholder="e.g. 30 x 40 ft, 500 sq ft"
                            value="{{ old('dimensions', $room->dimensions ?? '') }}"
                        />
                        <p class="text-base-content/60 text-sm mt-1">Size of the room (optional)</p>
                    </div>

                    <div class="divider text-xs text-base-content/50">Amenities</div>

                    {{-- Amenities --}}
                    <div>
                        <label class="label-text mb-3 block">Room Amenities</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            @php
                                $selectedAmenities = old('amenities', $room->amenities ?? []);
                            @endphp
                            @foreach($amenitiesList as $key => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="amenities[]"
                                    value="{{ $key }}"
                                    class="checkbox checkbox-primary checkbox-sm"
                                    {{ in_array($key, $selectedAmenities) ? 'checked' : '' }}
                                />
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('amenities')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-start gap-2 pt-4 border-t border-base-200">
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update Room' : 'Add Room' }}
                        </button>
                        <a href="{{ route('settings.locations.rooms') }}" class="btn btn-ghost">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
