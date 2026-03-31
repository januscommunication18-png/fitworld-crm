@extends('backoffice.layouts.app')

@section('title', 'Edit Partner')
@section('page-title', 'Edit Partner')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('backoffice.partners.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-xl font-semibold">Edit Partner</h1>
            <p class="text-base-content/60 text-sm">Update partner information</p>
        </div>
    </div>

    {{-- Remaining Percentage Info --}}
    <div class="alert alert-info">
        <span class="icon-[tabler--info-circle] size-5"></span>
        <span>Maximum percentage available for this partner: <strong>{{ number_format($remainingPercentage, 2) }}%</strong></span>
    </div>

    {{-- Form --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <form action="{{ route('backoffice.partners.update', $partner) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="label-text font-medium" for="name">Partner Name <span class="text-error">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $partner->name) }}" class="input input-bordered w-full mt-1 @error('name') input-error @enderror" placeholder="Enter partner name" required>
                    @error('name')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="label-text font-medium" for="email">Email Address <span class="text-error">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $partner->email) }}" class="input input-bordered w-full mt-1 @error('email') input-error @enderror" placeholder="partner@example.com" required>
                    @error('email')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Percentage --}}
                <div>
                    <label class="label-text font-medium" for="percentage">Percentage (%) <span class="text-error">*</span></label>
                    <div class="join w-full mt-1">
                        <input type="number" id="percentage" name="percentage" value="{{ old('percentage', $partner->percentage) }}" class="input input-bordered join-item flex-1 @error('percentage') input-error @enderror" placeholder="0.00" step="0.01" min="0.01" max="{{ $remainingPercentage }}" required>
                        <span class="btn btn-soft join-item pointer-events-none">%</span>
                    </div>
                    <p class="text-xs text-base-content/50 mt-1">Maximum: {{ number_format($remainingPercentage, 2) }}% (total cannot exceed 100%)</p>
                    @error('percentage')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Active Status --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="checkbox checkbox-primary" {{ old('is_active', $partner->is_active) ? 'checked' : '' }}>
                        <span class="label-text font-medium">Active</span>
                    </label>
                    <p class="text-xs text-base-content/50 mt-1 ml-9">Only active partners are counted towards the 100% total</p>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="label-text font-medium" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="textarea textarea-bordered w-full mt-1" rows="3" placeholder="Optional notes about this partner...">{{ old('notes', $partner->notes) }}</textarea>
                    @error('notes')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex justify-between pt-4 border-t border-base-200">
                    <form action="{{ route('backoffice.partners.destroy', $partner) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this partner?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-error btn-outline">
                            <span class="icon-[tabler--trash] size-4"></span>
                            Delete
                        </button>
                    </form>
                    <div class="flex gap-3">
                        <a href="{{ route('backoffice.partners.index') }}" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <span class="icon-[tabler--check] size-4"></span>
                            Update Partner
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
