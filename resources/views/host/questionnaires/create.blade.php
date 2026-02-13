@extends('layouts.settings')

@section('title', 'Create Questionnaire — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Create</li>
    </ol>
@endsection

@section('settings-content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('questionnaires.index') }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Create Questionnaire</h1>
            <p class="text-base-content/60 mt-1">Set up the basics, then build your form in the builder.</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('questionnaires.store') }}" method="POST" class="card bg-base-100">
        @csrf
        <div class="card-body space-y-6">
            {{-- Name --}}
            <div class="form-control">
                <label class="label" for="name">
                    <span class="label-text font-medium">Questionnaire Name <span class="text-error">*</span></span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="input input-bordered @error('name') input-error @enderror"
                       placeholder="e.g., New Client Intake Form" required>
                @error('name')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Description --}}
            <div class="form-control">
                <label class="label" for="description">
                    <span class="label-text font-medium">Description</span>
                    <span class="label-text-alt">Optional</span>
                </label>
                <textarea name="description" id="description" rows="3"
                          class="textarea textarea-bordered @error('description') textarea-error @enderror"
                          placeholder="Brief description of what this questionnaire is for...">{{ old('description') }}</textarea>
                @error('description')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Questionnaire Type <span class="text-error">*</span></span>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Single Page --}}
                    <label class="card bg-base-200/50 cursor-pointer hover:bg-base-200 transition-colors has-[:checked]:ring-2 has-[:checked]:ring-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="type" value="single" class="hidden" {{ old('type', 'single') === 'single' ? 'checked' : '' }}>
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-base-100">
                                    <span class="icon-[tabler--file-text] size-5 text-primary"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Single Page</h4>
                                    <p class="text-xs text-base-content/60">All questions on one page</p>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/60 mt-3">
                                Best for short forms with 5-12 questions. Client sees all questions at once and submits at the end.
                            </p>
                        </div>
                    </label>

                    {{-- Wizard --}}
                    <label class="card bg-base-200/50 cursor-pointer hover:bg-base-200 transition-colors has-[:checked]:ring-2 has-[:checked]:ring-primary has-[:checked]:bg-primary/5">
                        <input type="radio" name="type" value="wizard" class="hidden" {{ old('type') === 'wizard' ? 'checked' : '' }}>
                        <div class="card-body p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center bg-base-100">
                                    <span class="icon-[tabler--list-numbers] size-5 text-primary"></span>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Multi-Step Wizard</h4>
                                    <p class="text-xs text-base-content/60">Questions split into steps</p>
                                </div>
                            </div>
                            <p class="text-sm text-base-content/60 mt-3">
                                Best for longer forms. Client progresses through steps with auto-save and can resume later.
                            </p>
                        </div>
                    </label>
                </div>
                @error('type')
                    <span class="label-text-alt text-error mt-2">{{ $message }}</span>
                @enderror
            </div>

            {{-- Estimated Time --}}
            <div class="form-control">
                <label class="label" for="estimated_minutes">
                    <span class="label-text font-medium">Estimated Completion Time</span>
                    <span class="label-text-alt">Optional</span>
                </label>
                <div class="join">
                    <input type="number" name="estimated_minutes" id="estimated_minutes"
                           value="{{ old('estimated_minutes') }}"
                           class="input input-bordered join-item w-24 @error('estimated_minutes') input-error @enderror"
                           min="1" max="60" placeholder="5">
                    <span class="btn btn-disabled join-item">minutes</span>
                </div>
                <label class="label">
                    <span class="label-text-alt">Shown to clients so they know what to expect</span>
                </label>
                @error('estimated_minutes')
                    <span class="label-text-alt text-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-base-content/10">
                <a href="{{ route('questionnaires.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--arrow-right] size-5"></span>
                    Create & Build Form
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
