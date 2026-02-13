@extends('layouts.settings')

@section('title', 'Edit Questionnaire Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Settings</li>
    </ol>
@endsection

@section('settings-content')
<div class="max-w-2xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('questionnaires.show', $questionnaire) }}" class="btn btn-ghost btn-sm btn-circle">
            <span class="icon-[tabler--arrow-left] size-5"></span>
        </a>
        <div>
            <h1 class="text-2xl font-bold">Questionnaire Settings</h1>
            <p class="text-base-content/60 mt-1">{{ $questionnaire->name }}</p>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('questionnaires.update', $questionnaire) }}" method="POST" class="card bg-base-100">
        @csrf
        @method('PUT')
        <div class="card-body space-y-6">
            {{-- Basic Info --}}
            <div class="space-y-4">
                <h3 class="font-semibold text-lg">Basic Information</h3>

                {{-- Name --}}
                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text font-medium">Questionnaire Name <span class="text-error">*</span></span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $questionnaire->name) }}"
                           class="input input-bordered @error('name') input-error @enderror" required>
                    @error('name')
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Description --}}
                <div class="form-control">
                    <label class="label" for="description">
                        <span class="label-text font-medium">Description</span>
                    </label>
                    <textarea name="description" id="description" rows="3"
                              class="textarea textarea-bordered @error('description') textarea-error @enderror">{{ old('description', $questionnaire->description) }}</textarea>
                    @error('description')
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Type (read-only) --}}
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Questionnaire Type</span>
                    </label>
                    <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                        <span class="{{ $questionnaire->isWizard() ? 'icon-[tabler--list-numbers]' : 'icon-[tabler--file-text]' }} size-5 text-primary"></span>
                        <span>{{ $types[$questionnaire->type] }}</span>
                        <span class="badge badge-neutral badge-sm ml-auto">Cannot be changed</span>
                    </div>
                </div>

                {{-- Estimated Time --}}
                <div class="form-control">
                    <label class="label" for="estimated_minutes">
                        <span class="label-text font-medium">Estimated Completion Time</span>
                    </label>
                    <div class="join">
                        <input type="number" name="estimated_minutes" id="estimated_minutes"
                               value="{{ old('estimated_minutes', $questionnaire->estimated_minutes) }}"
                               class="input input-bordered join-item w-24" min="1" max="60" placeholder="5">
                        <span class="btn btn-disabled join-item">minutes</span>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Client Experience --}}
            <div class="space-y-4">
                <h3 class="font-semibold text-lg">Client Experience</h3>

                {{-- Intro Text --}}
                <div class="form-control">
                    <label class="label" for="intro_text">
                        <span class="label-text font-medium">Introduction Text</span>
                    </label>
                    <textarea name="intro_text" id="intro_text" rows="3"
                              class="textarea textarea-bordered @error('intro_text') textarea-error @enderror"
                              placeholder="Welcome! Please complete this questionnaire before your first visit...">{{ old('intro_text', $questionnaire->intro_text) }}</textarea>
                    <label class="label">
                        <span class="label-text-alt">Shown at the top of the questionnaire</span>
                    </label>
                </div>

                {{-- Thank You Message --}}
                <div class="form-control">
                    <label class="label" for="thank_you_message">
                        <span class="label-text font-medium">Thank You Message</span>
                    </label>
                    <textarea name="thank_you_message" id="thank_you_message" rows="3"
                              class="textarea textarea-bordered @error('thank_you_message') textarea-error @enderror"
                              placeholder="Thank you for completing the questionnaire! We look forward to seeing you.">{{ old('thank_you_message', $questionnaire->thank_you_message) }}</textarea>
                    <label class="label">
                        <span class="label-text-alt">Shown after client submits the form</span>
                    </label>
                </div>

                @if($questionnaire->isWizard())
                {{-- Allow Save & Resume --}}
                <div class="form-control">
                    <label class="label cursor-pointer justify-start gap-3" for="allow_save_resume">
                        <input type="checkbox" name="allow_save_resume" id="allow_save_resume" value="1"
                               class="toggle toggle-primary" {{ old('allow_save_resume', $questionnaire->allow_save_resume) ? 'checked' : '' }}>
                        <div>
                            <span class="label-text font-medium">Allow Save & Resume</span>
                            <p class="text-xs text-base-content/60">Let clients save progress and continue later</p>
                        </div>
                    </label>
                </div>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-base-content/10">
                <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-ghost">
                    <span class="icon-[tabler--edit] size-5"></span>
                    Edit Questions
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('questionnaires.show', $questionnaire) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </div>
        </div>
    </form>

    {{-- Danger Zone --}}
    <div class="card bg-base-100 border border-error/20">
        <div class="card-body">
            <h3 class="font-semibold text-lg text-error">Danger Zone</h3>
            <p class="text-sm text-base-content/60 mt-1">
                Deleting a questionnaire will also delete all associated responses. This action cannot be undone.
            </p>
            <div class="card-actions justify-end mt-4">
                <form action="{{ route('questionnaires.destroy', $questionnaire) }}" method="POST"
                      onsubmit="return confirm('Are you sure you want to delete this questionnaire? All responses will be lost.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error btn-outline">
                        <span class="icon-[tabler--trash] size-5"></span>
                        Delete Questionnaire
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
