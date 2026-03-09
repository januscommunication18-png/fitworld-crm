@extends('backoffice.layouts.app')

@section('title', 'Add Subscriber')
@section('page-title', 'Add Subscriber')

@section('content')
<div class="max-w-xl">
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-4">Add New Subscriber</h2>

            <form action="{{ route('backoffice.newsletter.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="first_name">First Name <span class="text-error">*</span></label>
                        <input type="text" id="first_name" name="first_name" class="input w-full @error('first_name') input-error @enderror"
                               value="{{ old('first_name') }}" maxlength="50" required>
                        @error('first_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" class="input w-full @error('last_name') input-error @enderror"
                               value="{{ old('last_name') }}" maxlength="50">
                        @error('last_name')
                            <p class="text-error text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="email">Email <span class="text-error">*</span></label>
                    <input type="email" id="email" name="email" class="input w-full @error('email') input-error @enderror"
                           value="{{ old('email') }}" required>
                    @error('email')
                        <p class="text-error text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="{{ route('backoffice.newsletter.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Subscriber
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
