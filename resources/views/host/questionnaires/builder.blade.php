@extends('layouts.settings')

@push('head')
    @vite(['resources/js/questionnaire-builder.js'])
@endpush

@push('styles')
<style>
    /* Drag and drop styles */
    .draggable-question-type {
        user-select: none;
        -webkit-user-select: none;
    }
    .draggable-question-type.dragging {
        opacity: 0.5;
    }
    .question-drop-zone.drag-over {
        background-color: oklch(var(--p) / 0.1);
        border-color: oklch(var(--p));
    }
    .question-drop-zone .empty-questions-placeholder.drag-over {
        border-color: oklch(var(--p));
        background-color: oklch(var(--p) / 0.15);
    }
</style>
@endpush

@section('title', 'Build Questionnaire — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Builder</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6" id="questionnaire-builder" data-questionnaire-id="{{ $questionnaire->id }}" data-version-id="{{ $version->id }}">
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('questionnaires.show', $questionnaire) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold">{{ $questionnaire->name }}</h1>
                    <span class="badge badge-warning">Draft v{{ $version->version_number }}</span>
                </div>
                <p class="text-base-content/60 mt-1">
                    {{ $questionnaire->isWizard() ? 'Multi-Step Wizard' : 'Single Page' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('questionnaires.preview', $questionnaire) }}" class="btn btn-ghost" target="_blank">
                <span class="icon-[tabler--eye] size-5"></span>
                Preview
            </a>
            <a href="{{ route('questionnaires.edit', $questionnaire) }}" class="btn btn-ghost">
                <span class="icon-[tabler--settings] size-5"></span>
                Settings
            </a>
            <form action="{{ route('questionnaires.publish', $questionnaire) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <span class="icon-[tabler--rocket] size-5"></span>
                    Publish
                </button>
            </form>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Left Panel: Question Types --}}
        <div class="lg:col-span-1">
            @if($questionnaire->isWizard())
            {{-- Wizard Mode Guide --}}
            <div class="card bg-gradient-to-br from-primary/5 to-primary/10 border border-primary/20 mb-4">
                <div class="card-body p-4">
                    <h4 class="font-semibold text-sm flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
                        How to Build Your Form
                    </h4>
                    <div class="space-y-2">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">1</div>
                            <div>
                                <p class="text-sm font-medium">Add Step</p>
                                <p class="text-xs text-base-content/60">Create wizard pages</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <span class="icon-[tabler--arrow-down] size-4 text-base-content/30"></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">2</div>
                            <div>
                                <p class="text-sm font-medium">Add Section</p>
                                <p class="text-xs text-base-content/60">Group related questions</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <span class="icon-[tabler--arrow-down] size-4 text-base-content/30"></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-6 h-6 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">3</div>
                            <div>
                                <p class="text-sm font-medium">Add Questions</p>
                                <p class="text-xs text-base-content/60">Build your form fields</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="card bg-base-100 sticky top-20">
                <div class="card-body">
                    <h3 class="font-semibold mb-2">Add Question</h3>
                    <p class="text-xs text-base-content/50 mb-4">Drag into a section or click to add</p>
                    <div class="space-y-2" id="question-types-list">
                        @foreach($questionTypes as $type => $label)
                            <div class="draggable-question-type flex items-center gap-3 px-3 py-2 rounded-lg border border-base-200 bg-base-100 hover:border-primary/30 hover:bg-primary/5 cursor-grab active:cursor-grabbing transition-all"
                                 draggable="true"
                                 data-type="{{ $type }}">
                                <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
                                <span class="{{ \App\Models\QuestionnaireQuestion::getQuestionTypeIcon($type) }} size-5 text-primary"></span>
                                <span class="text-sm font-medium">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="divider my-4"></div>

                    <button type="button" class="btn btn-outline btn-block gap-2" id="add-block-btn">
                        <span class="icon-[tabler--layout-grid-add] size-5"></span>
                        Add Section Block
                    </button>

                    @if($questionnaire->isWizard())
                        <button type="button" class="btn btn-outline btn-block gap-2 mt-2" id="add-step-btn">
                            <span class="icon-[tabler--list-numbers] size-5"></span>
                            Add Step
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Panel: Form Builder --}}
        <div class="lg:col-span-2">
            @if($questionnaire->isWizard())
                {{-- Wizard Mode: Steps --}}
                <div id="steps-container" class="space-y-4">
                    @forelse($version->steps as $stepIndex => $step)
                        @include('host.questionnaires.partials.step-card', ['step' => $step, 'stepIndex' => $stepIndex])
                    @empty
                        <div class="card bg-base-100 border-2 border-dashed border-primary/30" id="empty-steps-placeholder">
                            <div class="card-body text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary/10 mx-auto mb-4">
                                    <span class="icon-[tabler--list-numbers] size-8 text-primary"></span>
                                </div>
                                <div class="inline-flex items-center gap-2 bg-primary/10 text-primary text-sm font-medium px-3 py-1 rounded-full mb-3">
                                    <span class="w-5 h-5 rounded-full bg-primary text-primary-content flex items-center justify-center text-xs font-bold">1</span>
                                    Start Here
                                </div>
                                <h3 class="font-semibold mb-2">Add Your First Step</h3>
                                <p class="text-base-content/60 mb-4 max-w-sm mx-auto">Steps create pages in your wizard. Each step can contain multiple sections with questions.</p>
                                <button type="button" class="btn btn-primary mx-auto" id="add-first-step-btn">
                                    <span class="icon-[tabler--plus] size-5"></span>
                                    Add First Step
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            @else
                {{-- Single Page Mode: Blocks Only --}}
                <div id="blocks-container" class="space-y-4">
                    @forelse($version->blocks as $blockIndex => $block)
                        @include('host.questionnaires.partials.block-card', ['block' => $block, 'blockIndex' => $blockIndex])
                    @empty
                        <div class="card bg-base-100 border-2 border-dashed border-base-content/20" id="empty-blocks-placeholder">
                            <div class="card-body text-center py-12">
                                <span class="icon-[tabler--layout-grid] size-12 text-base-content/20 mx-auto mb-3"></span>
                                <h3 class="font-semibold mb-2">No Questions Yet</h3>
                                <p class="text-base-content/60 mb-4">Add a section block, then add questions inside it.</p>
                                <button type="button" class="btn btn-primary btn-sm mx-auto" id="add-first-block-btn">
                                    <span class="icon-[tabler--plus] size-5"></span>
                                    Add First Section
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Add Block Modal --}}
<div id="add-block-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('add-block-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <h3 class="font-bold text-lg mb-4">Add Section Block</h3>
                <form id="add-block-form" class="space-y-4">
                    <input type="hidden" name="step_id" id="block-step-id">
                    <div class="form-control">
                        <label class="label" for="block-title">
                            <span class="label-text font-medium">Section Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" id="block-title" class="input input-bordered" required
                               placeholder="e.g., Health Information">
                    </div>
                    <div class="form-control">
                        <label class="label" for="block-description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" id="block-description" class="textarea textarea-bordered" rows="2"
                                  placeholder="Optional description for this section..."></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label" for="block-style">
                            <span class="label-text font-medium">Display Style</span>
                        </label>
                        <select name="display_style" id="block-style" class="select select-bordered">
                            <option value="plain">Plain</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('add-block-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Add Question Modal --}}
<div id="add-question-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/60 transition-opacity" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" onclick="closeModal('add-question-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none overflow-y-auto">
        <div class="bg-base-100 rounded-2xl shadow-2xl max-w-2xl w-full pointer-events-auto transform transition-all my-8">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--help-circle] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Add Question</h3>
                        <p class="text-sm text-base-content/60">Create a new question for your form</p>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeModal('add-question-modal')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <form id="add-question-form">
                <input type="hidden" name="block_id" id="question-block-id">

                <div class="p-5 space-y-5 max-h-[calc(100vh-220px)] overflow-y-auto">
                    {{-- Question Type Selector --}}
                    <div id="question-type-selector">
                        <label class="text-sm font-medium text-base-content mb-3 block">
                            Question Type <span class="text-error">*</span>
                        </label>
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2" id="question-type-grid">
                            @php
                                $typeIcons = [
                                    'short_text' => 'icon-[tabler--text-size]',
                                    'long_text' => 'icon-[tabler--align-left]',
                                    'email' => 'icon-[tabler--mail]',
                                    'phone' => 'icon-[tabler--phone]',
                                    'number' => 'icon-[tabler--123]',
                                    'date' => 'icon-[tabler--calendar]',
                                    'yes_no' => 'icon-[tabler--toggle-left]',
                                    'single_select' => 'icon-[tabler--circle-dot]',
                                    'multi_select' => 'icon-[tabler--checkbox]',
                                    'dropdown' => 'icon-[tabler--selector]',
                                    'acknowledgement' => 'icon-[tabler--checklist]',
                                ];
                            @endphp
                            @foreach($questionTypes as $type => $label)
                                <button type="button"
                                        class="question-type-btn flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 border-base-200 bg-base-100 hover:border-primary/30 hover:bg-primary/5 transition-all"
                                        data-type="{{ $type }}">
                                    <span class="{{ $typeIcons[$type] ?? 'icon-[tabler--forms]' }} size-5 text-base-content/70"></span>
                                    <span class="text-xs font-medium text-center leading-tight">{{ $label }}</span>
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" id="question-type" name="question_type" required>
                    </div>

                    {{-- Question Text --}}
                    <div class="bg-base-200/30 rounded-xl p-4 space-y-4">
                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="question-label">
                                Question Text <span class="text-error">*</span>
                            </label>
                            <input type="text" name="question_label" id="question-label"
                                   class="input input-bordered w-full focus:input-primary" required
                                   placeholder="e.g., Do you have any injuries we should know about?">
                        </div>

                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="question-help">
                                Help Text <span class="text-base-content/40 font-normal">(optional)</span>
                            </label>
                            <input type="text" name="help_text" id="question-help"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="Additional instructions shown below the question...">
                        </div>
                    </div>

                    {{-- Options for select types --}}
                    <div id="options-container" class="hidden">
                        <div class="bg-base-200/30 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-medium text-base-content">
                                    Answer Options <span class="text-error">*</span>
                                </label>
                                <button type="button" class="btn btn-ghost btn-xs gap-1" id="add-option-btn">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add Option
                                </button>
                            </div>
                            <div id="options-list" class="space-y-2">
                                <div class="flex items-center gap-2 option-row group">
                                    <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
                                    <input type="text" name="options[]" class="input input-bordered input-sm flex-1" placeholder="Option 1">
                                    <button type="button" class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 remove-option-btn">
                                        <span class="icon-[tabler--trash] size-4 text-error"></span>
                                    </button>
                                </div>
                                <div class="flex items-center gap-2 option-row group">
                                    <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
                                    <input type="text" name="options[]" class="input input-bordered input-sm flex-1" placeholder="Option 2">
                                    <button type="button" class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 remove-option-btn">
                                        <span class="icon-[tabler--trash] size-4 text-error"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Settings Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="question-placeholder">
                                Placeholder <span class="text-base-content/40 font-normal">(optional)</span>
                            </label>
                            <input type="text" name="placeholder" id="question-placeholder"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="e.g., Type your answer...">
                        </div>

                        <div class="flex items-end pb-1">
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-base-200 hover:border-primary/30 hover:bg-primary/5 transition-all w-full">
                                <input type="checkbox" name="is_required" id="question-required" class="checkbox checkbox-primary checkbox-sm mt-0.5">
                                <div>
                                    <span class="text-sm font-medium">Required</span>
                                    <p class="text-xs text-base-content/50">Must be answered</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Advanced Options --}}
                    <details class="group">
                        <summary class="flex items-center gap-2 cursor-pointer text-sm font-medium text-base-content/70 hover:text-base-content transition-colors py-2">
                            <span class="icon-[tabler--settings] size-4"></span>
                            Advanced Settings
                            <span class="icon-[tabler--chevron-down] size-4 ml-auto group-open:rotate-180 transition-transform"></span>
                        </summary>
                        <div class="pt-3 pb-1 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-base-200 hover:border-warning/50 hover:bg-warning/5 transition-all">
                                    <input type="checkbox" name="is_sensitive" id="question-sensitive" class="checkbox checkbox-warning checkbox-sm mt-0.5">
                                    <div>
                                        <span class="text-sm font-medium flex items-center gap-1.5">
                                            <span class="icon-[tabler--shield-lock] size-4 text-warning"></span>
                                            Sensitive
                                        </span>
                                        <p class="text-xs text-base-content/50 mt-0.5">Only visible to admins & assigned staff</p>
                                    </div>
                                </label>

                                <div class="form-control">
                                    <label class="text-sm font-medium text-base-content mb-2 block" for="question-visibility">
                                        Visibility
                                    </label>
                                    <select name="visibility" id="question-visibility" class="select select-bordered select-sm w-full">
                                        <option value="client">Visible to Client</option>
                                        <option value="instructor_only">Instructor Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 p-5 border-t border-base-200 bg-base-200/30">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('add-question-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Add Step Modal (Wizard only) --}}
@if($questionnaire->isWizard())
<div id="add-step-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('add-step-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <h3 class="font-bold text-lg mb-4">Add Step</h3>
                <form id="add-step-form" class="space-y-4">
                    <div class="form-control">
                        <label class="label" for="step-title">
                            <span class="label-text font-medium">Step Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" id="step-title" class="input input-bordered" required
                               placeholder="e.g., Personal Information">
                    </div>
                    <div class="form-control">
                        <label class="label" for="step-description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" id="step-description" class="textarea textarea-bordered" rows="2"
                                  placeholder="Optional description for this step..."></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('add-step-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Step</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Edit Question Modal --}}
<div id="edit-question-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/60 transition-opacity" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);" onclick="closeModal('edit-question-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none overflow-y-auto">
        <div class="bg-base-100 rounded-2xl shadow-2xl max-w-2xl w-full pointer-events-auto transform transition-all my-8">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-5 border-b border-base-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <span class="icon-[tabler--edit] size-5 text-primary"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Edit Question</h3>
                        <p class="text-sm text-base-content/60">Modify this question's settings</p>
                    </div>
                </div>
                <button type="button" class="btn btn-ghost btn-sm btn-circle" onclick="closeModal('edit-question-modal')">
                    <span class="icon-[tabler--x] size-5"></span>
                </button>
            </div>

            <form id="edit-question-form">
                <input type="hidden" name="question_id" id="edit-question-id">

                <div class="p-5 space-y-5 max-h-[calc(100vh-220px)] overflow-y-auto">
                    {{-- Question Type Badge (readonly) --}}
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-base-content/60">Question Type:</span>
                        <span id="edit-question-type-badge" class="badge badge-primary badge-outline gap-1">
                            <span id="edit-question-type-icon" class="size-4"></span>
                            <span id="edit-question-type-label">Short Text</span>
                        </span>
                    </div>

                    {{-- Question Text --}}
                    <div class="bg-base-200/30 rounded-xl p-4 space-y-4">
                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="edit-question-label">
                                Question Text <span class="text-error">*</span>
                            </label>
                            <input type="text" name="question_label" id="edit-question-label"
                                   class="input input-bordered w-full focus:input-primary" required
                                   placeholder="e.g., Do you have any injuries we should know about?">
                        </div>

                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="edit-question-help">
                                Help Text <span class="text-base-content/40 font-normal">(optional)</span>
                            </label>
                            <input type="text" name="help_text" id="edit-question-help"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="Additional instructions shown below the question...">
                        </div>
                    </div>

                    {{-- Options for select types --}}
                    <div id="edit-options-container" class="hidden">
                        <div class="bg-base-200/30 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-sm font-medium text-base-content">
                                    Answer Options <span class="text-error">*</span>
                                </label>
                                <button type="button" class="btn btn-ghost btn-xs gap-1" id="edit-add-option-btn">
                                    <span class="icon-[tabler--plus] size-4"></span>
                                    Add Option
                                </button>
                            </div>
                            <div id="edit-options-list" class="space-y-2"></div>
                        </div>
                    </div>

                    {{-- Settings Row --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-control">
                            <label class="text-sm font-medium text-base-content mb-2 block" for="edit-question-placeholder">
                                Placeholder <span class="text-base-content/40 font-normal">(optional)</span>
                            </label>
                            <input type="text" name="placeholder" id="edit-question-placeholder"
                                   class="input input-bordered input-sm w-full"
                                   placeholder="e.g., Type your answer...">
                        </div>

                        <div class="flex items-end pb-1">
                            <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-base-200 hover:border-primary/30 hover:bg-primary/5 transition-all w-full">
                                <input type="checkbox" name="is_required" id="edit-question-required" class="checkbox checkbox-primary checkbox-sm mt-0.5">
                                <div>
                                    <span class="text-sm font-medium">Required</span>
                                    <p class="text-xs text-base-content/50">Must be answered</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Advanced Options --}}
                    <details class="group">
                        <summary class="flex items-center gap-2 cursor-pointer text-sm font-medium text-base-content/70 hover:text-base-content transition-colors py-2">
                            <span class="icon-[tabler--settings] size-4"></span>
                            Advanced Settings
                            <span class="icon-[tabler--chevron-down] size-4 ml-auto group-open:rotate-180 transition-transform"></span>
                        </summary>
                        <div class="pt-3 pb-1 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-start gap-3 cursor-pointer p-3 rounded-lg border border-base-200 hover:border-warning/50 hover:bg-warning/5 transition-all">
                                    <input type="checkbox" name="is_sensitive" id="edit-question-sensitive" class="checkbox checkbox-warning checkbox-sm mt-0.5">
                                    <div>
                                        <span class="text-sm font-medium flex items-center gap-1.5">
                                            <span class="icon-[tabler--shield-lock] size-4 text-warning"></span>
                                            Sensitive
                                        </span>
                                        <p class="text-xs text-base-content/50 mt-0.5">Only visible to admins & assigned staff</p>
                                    </div>
                                </label>

                                <div class="form-control">
                                    <label class="text-sm font-medium text-base-content mb-2 block" for="edit-question-visibility">
                                        Visibility
                                    </label>
                                    <select name="visibility" id="edit-question-visibility" class="select select-bordered select-sm w-full">
                                        <option value="client">Visible to Client</option>
                                        <option value="instructor_only">Instructor Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 p-5 border-t border-base-200 bg-base-200/30">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('edit-question-modal')">Cancel</button>
                    <button type="submit" class="btn btn-primary gap-2">
                        <span class="icon-[tabler--device-floppy] size-5"></span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Block Modal --}}
<div id="edit-block-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('edit-block-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <h3 class="font-bold text-lg mb-4">Edit Section Block</h3>
                <form id="edit-block-form" class="space-y-4">
                    <input type="hidden" name="block_id" id="edit-block-id">
                    <div class="form-control">
                        <label class="label" for="edit-block-title">
                            <span class="label-text font-medium">Section Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" id="edit-block-title" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="edit-block-description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" id="edit-block-description" class="textarea textarea-bordered" rows="2"></textarea>
                    </div>
                    <div class="form-control">
                        <label class="label" for="edit-block-style">
                            <span class="label-text font-medium">Display Style</span>
                        </label>
                        <select name="display_style" id="edit-block-style" class="select select-bordered">
                            <option value="plain">Plain</option>
                            <option value="card">Card</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('edit-block-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Edit Step Modal (Wizard only) --}}
@if($questionnaire->isWizard())
<div id="edit-step-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('edit-step-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-md w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <h3 class="font-bold text-lg mb-4">Edit Step</h3>
                <form id="edit-step-form" class="space-y-4">
                    <input type="hidden" name="step_id" id="edit-step-id">
                    <div class="form-control">
                        <label class="label" for="edit-step-title">
                            <span class="label-text font-medium">Step Title <span class="text-error">*</span></span>
                        </label>
                        <input type="text" name="title" id="edit-step-title" class="input input-bordered" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="edit-step-description">
                            <span class="label-text font-medium">Description</span>
                        </label>
                        <textarea name="description" id="edit-step-description" class="textarea textarea-bordered" rows="2"></textarea>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" class="btn btn-ghost" onclick="closeModal('edit-step-modal')">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Confirm Delete Modal --}}
<div id="confirm-delete-modal" class="hidden fixed inset-0 z-50">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeModal('confirm-delete-modal')"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4 pointer-events-none">
        <div class="bg-base-100 rounded-2xl shadow-xl max-w-sm w-full pointer-events-auto transform transition-all">
            <div class="p-6">
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center mb-4">
                        <span class="icon-[tabler--alert-triangle] size-8 text-error"></span>
                    </div>
                    <h3 class="font-bold text-lg mb-2">Confirm Delete</h3>
                    <p class="text-base-content/60 text-sm" id="delete-message">Are you sure you want to delete this item?</p>
                </div>
                <div class="flex justify-center gap-3 mt-6">
                    <button type="button" class="btn btn-ghost" onclick="closeModal('confirm-delete-modal')">Cancel</button>
                    <button type="button" class="btn btn-error" id="confirm-delete-btn">Delete</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Modal helper functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.fixed.inset-0.z-50:not(.hidden)').forEach(modal => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        });
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const questionnaireId = document.getElementById('questionnaire-builder').dataset.questionnaireId;
    const versionId = document.getElementById('questionnaire-builder').dataset.versionId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    // Show options container for select-type questions
    const selectTypes = ['single_select', 'multi_select', 'dropdown'];
    const optionsContainer = document.getElementById('options-container');

    // Add Block Modal
    const addBlockForm = document.getElementById('add-block-form');
    const isWizardMode = {{ $questionnaire->isWizard() ? 'true' : 'false' }};

    document.getElementById('add-block-btn')?.addEventListener('click', () => {
        if (isWizardMode) {
            // In wizard mode, check if steps exist
            const firstStep = document.querySelector('[data-step-id]');
            if (firstStep) {
                // Auto-select the first step
                document.getElementById('block-step-id').value = firstStep.dataset.stepId;
                openModal('add-block-modal');
            } else {
                alert('Please add a step first, then add sections inside that step.');
            }
        } else {
            document.getElementById('block-step-id').value = '';
            openModal('add-block-modal');
        }
    });

    document.getElementById('add-first-block-btn')?.addEventListener('click', () => {
        document.getElementById('block-step-id').value = '';
        openModal('add-block-modal');
    });

    addBlockForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addBlockForm);

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/blocks`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    questionnaire_version_id: versionId,
                    step_id: formData.get('step_id') || null,
                    title: formData.get('title'),
                    description: formData.get('description'),
                    display_style: formData.get('display_style'),
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to add block');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add block. Please try again.');
        }
    });

    // Add Question Modal
    const addQuestionForm = document.getElementById('add-question-form');
    const questionTypeSelector = document.getElementById('question-type-selector');
    const questionTypeInput = document.getElementById('question-type');
    const questionTypeGrid = document.getElementById('question-type-grid');

    // Handle question type button clicks
    questionTypeGrid?.addEventListener('click', (e) => {
        const btn = e.target.closest('.question-type-btn');
        if (!btn) return;

        const type = btn.dataset.type;

        // Update hidden input
        questionTypeInput.value = type;

        // Update visual selection
        questionTypeGrid.querySelectorAll('.question-type-btn').forEach(b => {
            b.classList.remove('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/20');
            b.classList.add('border-base-200', 'bg-base-100');
            b.querySelector('span:first-child').classList.remove('text-primary');
            b.querySelector('span:first-child').classList.add('text-base-content/70');
        });

        btn.classList.remove('border-base-200', 'bg-base-100');
        btn.classList.add('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/20');
        btn.querySelector('span:first-child').classList.remove('text-base-content/70');
        btn.querySelector('span:first-child').classList.add('text-primary');

        // Show/hide options container
        if (selectTypes.includes(type)) {
            optionsContainer.classList.remove('hidden');
        } else {
            optionsContainer.classList.add('hidden');
        }
    });

    // Helper to select a question type
    function selectQuestionType(type) {
        const btn = questionTypeGrid?.querySelector(`.question-type-btn[data-type="${type}"]`);
        if (btn) {
            btn.click();
        }
    }

    // Helper to clear question type selection
    function clearQuestionTypeSelection() {
        questionTypeInput.value = '';
        questionTypeGrid?.querySelectorAll('.question-type-btn').forEach(b => {
            b.classList.remove('border-primary', 'bg-primary/10', 'ring-2', 'ring-primary/20');
            b.classList.add('border-base-200', 'bg-base-100');
            b.querySelector('span:first-child').classList.remove('text-primary');
            b.querySelector('span:first-child').classList.add('text-base-content/70');
        });
    }

    // Side panel question type buttons (hide selector, pre-select type)
    document.querySelectorAll('.add-question-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;

            // Reset form
            addQuestionForm.reset();
            clearQuestionTypeSelection();

            // Select the question type
            selectQuestionType(type);

            // Hide question type selector since type is pre-selected
            questionTypeSelector.classList.add('hidden');

            // Get first block if exists
            const firstBlock = document.querySelector('[data-block-id]');
            if (firstBlock) {
                document.getElementById('question-block-id').value = firstBlock.dataset.blockId;
                openModal('add-question-modal');
            } else {
                // Better error message for wizard mode
                if (isWizardMode) {
                    const firstStep = document.querySelector('[data-step-id]');
                    if (firstStep) {
                        alert('Please add a section inside your step first.\n\nClick the "Add Section" button inside a step, then you can add questions.');
                    } else {
                        alert('Please add a step first, then add a section inside it.');
                    }
                } else {
                    alert('Please add a section block first.');
                }
            }
        });
    });

    // "Add Question to this Section" buttons (show selector, set specific block)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-question-to-block-btn');
        if (btn) {
            const blockId = btn.dataset.blockId;

            // Reset form and clear selection
            addQuestionForm.reset();
            clearQuestionTypeSelection();

            // Show question type selector
            questionTypeSelector.classList.remove('hidden');

            // Hide options container initially
            optionsContainer.classList.add('hidden');

            // Set the block ID
            document.getElementById('question-block-id').value = blockId;

            openModal('add-question-modal');
        }
    });

    // "Add Block to Step" buttons (wizard mode)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.add-block-to-step-btn');
        if (btn) {
            e.preventDefault();
            const stepId = btn.dataset.stepId;
            document.getElementById('block-step-id').value = stepId;
            openModal('add-block-modal');
        }
    });

    // Add option button
    document.getElementById('add-option-btn')?.addEventListener('click', () => {
        const optionsList = document.getElementById('options-list');
        const optionCount = optionsList.querySelectorAll('.option-row').length + 1;
        const newOption = document.createElement('div');
        newOption.className = 'flex items-center gap-2 option-row group';
        newOption.innerHTML = `
            <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
            <input type="text" name="options[]" class="input input-bordered input-sm flex-1" placeholder="Option ${optionCount}">
            <button type="button" class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 remove-option-btn">
                <span class="icon-[tabler--trash] size-4 text-error"></span>
            </button>
        `;
        optionsList.appendChild(newOption);

        newOption.querySelector('.remove-option-btn').addEventListener('click', () => {
            newOption.remove();
        });
    });

    // Remove option buttons (delegated)
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.remove-option-btn');
        if (btn && btn.closest('#options-list')) {
            btn.closest('.option-row').remove();
        }
    });

    addQuestionForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addQuestionForm);

        // Collect options
        const options = [];
        formData.getAll('options[]').forEach((opt, idx) => {
            if (opt.trim()) {
                options.push({ key: `option_${idx + 1}`, label: opt.trim() });
            }
        });

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/questions`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    questionnaire_block_id: formData.get('block_id'),
                    question_label: formData.get('question_label'),
                    question_type: formData.get('question_type'),
                    placeholder: formData.get('placeholder'),
                    help_text: formData.get('help_text'),
                    is_required: formData.has('is_required'),
                    is_sensitive: formData.has('is_sensitive'),
                    visibility: formData.get('visibility'),
                    options: options.length > 0 ? options : null,
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to add question');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add question. Please try again.');
        }
    });

    // Add Step Modal (Wizard only)
    const addStepForm = document.getElementById('add-step-form');

    document.getElementById('add-step-btn')?.addEventListener('click', () => {
        openModal('add-step-modal');
    });

    document.getElementById('add-first-step-btn')?.addEventListener('click', () => {
        openModal('add-step-modal');
    });

    addStepForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(addStepForm);

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/steps`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    questionnaire_version_id: versionId,
                    title: formData.get('title'),
                    description: formData.get('description'),
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to add step');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to add step. Please try again.');
        }
    });

    // ==================== EDIT HANDLERS ====================

    // Store question data for editing (include both direct blocks and blocks within steps)
    const questionsData = @json(
        $questionnaire->isWizard()
            ? $version->steps->flatMap(fn($step) => $step->blocks)->flatMap(fn($block) => $block->questions)->keyBy('id')
            : $version->blocks->flatMap(fn($block) => $block->questions)->keyBy('id')
    );

    // Question type icons and labels for edit modal
    const questionTypeIcons = {
        'short_text': 'icon-[tabler--text-size]',
        'long_text': 'icon-[tabler--align-left]',
        'email': 'icon-[tabler--mail]',
        'phone': 'icon-[tabler--phone]',
        'number': 'icon-[tabler--123]',
        'date': 'icon-[tabler--calendar]',
        'yes_no': 'icon-[tabler--toggle-left]',
        'single_select': 'icon-[tabler--circle-dot]',
        'multi_select': 'icon-[tabler--checkbox]',
        'dropdown': 'icon-[tabler--selector]',
        'acknowledgement': 'icon-[tabler--checklist]',
    };

    const questionTypeLabels = {
        'short_text': 'Short Text',
        'long_text': 'Long Text',
        'email': 'Email',
        'phone': 'Phone',
        'number': 'Number',
        'date': 'Date',
        'yes_no': 'Yes/No',
        'single_select': 'Single Select',
        'multi_select': 'Multi Select',
        'dropdown': 'Dropdown',
        'acknowledgement': 'Acknowledgement',
    };

    // Edit Question - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.edit-question-btn');
        if (btn) {
            e.preventDefault();
            const questionId = btn.dataset.questionId;
            const question = questionsData[questionId];

            if (question) {
                document.getElementById('edit-question-id').value = questionId;
                document.getElementById('edit-question-label').value = question.question_label || '';
                document.getElementById('edit-question-placeholder').value = question.placeholder || '';
                document.getElementById('edit-question-help').value = question.help_text || '';
                document.getElementById('edit-question-required').checked = question.is_required;
                document.getElementById('edit-question-sensitive').checked = question.is_sensitive;
                document.getElementById('edit-question-visibility').value = question.visibility || 'client';

                // Update question type badge
                const typeIcon = questionTypeIcons[question.question_type] || 'icon-[tabler--forms]';
                const typeLabel = questionTypeLabels[question.question_type] || question.question_type;
                document.getElementById('edit-question-type-icon').className = typeIcon + ' size-4';
                document.getElementById('edit-question-type-label').textContent = typeLabel;

                // Handle options for select types
                const editOptionsContainer = document.getElementById('edit-options-container');
                const editOptionsList = document.getElementById('edit-options-list');

                if (selectTypes.includes(question.question_type) && question.options) {
                    editOptionsContainer.classList.remove('hidden');
                    editOptionsList.innerHTML = '';
                    question.options.forEach((opt, idx) => {
                        const optionRow = document.createElement('div');
                        optionRow.className = 'flex items-center gap-2 option-row group';
                        optionRow.innerHTML = `
                            <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
                            <input type="text" name="options[]" class="input input-bordered input-sm flex-1" value="${opt.label || opt}" placeholder="Option ${idx + 1}">
                            <button type="button" class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 remove-option-btn">
                                <span class="icon-[tabler--trash] size-4 text-error"></span>
                            </button>
                        `;
                        editOptionsList.appendChild(optionRow);
                        optionRow.querySelector('.remove-option-btn').addEventListener('click', () => optionRow.remove());
                    });
                } else {
                    editOptionsContainer.classList.add('hidden');
                    editOptionsList.innerHTML = '';
                }

                openModal('edit-question-modal');
            }
        }
    });

    // Edit Question - add option button
    document.getElementById('edit-add-option-btn')?.addEventListener('click', () => {
        const editOptionsList = document.getElementById('edit-options-list');
        const optionCount = editOptionsList.querySelectorAll('.option-row').length + 1;
        const newOption = document.createElement('div');
        newOption.className = 'flex items-center gap-2 option-row group';
        newOption.innerHTML = `
            <span class="icon-[tabler--grip-vertical] size-4 text-base-content/30"></span>
            <input type="text" name="options[]" class="input input-bordered input-sm flex-1" placeholder="Option ${optionCount}">
            <button type="button" class="btn btn-ghost btn-sm btn-square opacity-0 group-hover:opacity-100 remove-option-btn">
                <span class="icon-[tabler--trash] size-4 text-error"></span>
            </button>
        `;
        editOptionsList.appendChild(newOption);
        newOption.querySelector('.remove-option-btn').addEventListener('click', () => newOption.remove());
    });

    // Edit Question - form submit
    document.getElementById('edit-question-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const questionId = formData.get('question_id');

        // Collect options
        const options = [];
        formData.getAll('options[]').forEach((opt, idx) => {
            if (opt.trim()) {
                options.push({ key: `option_${idx + 1}`, label: opt.trim() });
            }
        });

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/questions/${questionId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    question_label: formData.get('question_label'),
                    placeholder: formData.get('placeholder'),
                    help_text: formData.get('help_text'),
                    is_required: form.querySelector('[name="is_required"]').checked,
                    is_sensitive: form.querySelector('[name="is_sensitive"]').checked,
                    visibility: formData.get('visibility'),
                    options: options.length > 0 ? options : null,
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to update question');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update question. Please try again.');
        }
    });

    // Store block data for editing (include both direct blocks and blocks within steps)
    const blocksData = @json(
        $questionnaire->isWizard()
            ? $version->steps->flatMap(fn($step) => $step->blocks)->keyBy('id')
            : $version->blocks->keyBy('id')
    );

    // Edit Block - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.edit-block-btn');
        if (btn) {
            e.preventDefault();
            const blockId = btn.dataset.blockId;
            const block = blocksData[blockId];

            if (block) {
                document.getElementById('edit-block-id').value = blockId;
                document.getElementById('edit-block-title').value = block.title || '';
                document.getElementById('edit-block-description').value = block.description || '';
                document.getElementById('edit-block-style').value = block.display_style || 'plain';
                openModal('edit-block-modal');
            }
        }
    });

    // Edit Block - form submit
    document.getElementById('edit-block-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const blockId = formData.get('block_id');

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/blocks/${blockId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: formData.get('title'),
                    description: formData.get('description'),
                    display_style: formData.get('display_style'),
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to update block');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update block. Please try again.');
        }
    });

    // Store step data for editing (wizard mode only)
    const stepsData = @json($version->steps->keyBy('id') ?? collect());

    // Edit Step - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.edit-step-btn');
        if (btn) {
            e.preventDefault();
            const stepId = btn.dataset.stepId;
            const step = stepsData[stepId];

            if (step) {
                document.getElementById('edit-step-id').value = stepId;
                document.getElementById('edit-step-title').value = step.title || '';
                document.getElementById('edit-step-description').value = step.description || '';
                openModal('edit-step-modal');
            }
        }
    });

    // Edit Step - form submit
    document.getElementById('edit-step-form')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const stepId = formData.get('step_id');

        try {
            const response = await fetch(`/api/v1/questionnaires/${questionnaireId}/steps/${stepId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    title: formData.get('title'),
                    description: formData.get('description'),
                }),
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to update step');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to update step. Please try again.');
        }
    });

    // ==================== DELETE HANDLERS ====================

    let deleteType = null;
    let deleteId = null;

    function showDeleteConfirm(type, id, message) {
        deleteType = type;
        deleteId = id;
        document.getElementById('delete-message').textContent = message;
        openModal('confirm-delete-modal');
    }

    // Delete Question - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-question-btn');
        if (btn) {
            e.preventDefault();
            showDeleteConfirm('question', btn.dataset.questionId, 'Are you sure you want to delete this question? This action cannot be undone.');
        }
    });

    // Delete Block - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-block-btn');
        if (btn) {
            e.preventDefault();
            showDeleteConfirm('block', btn.dataset.blockId, 'Are you sure you want to delete this section and all its questions? This action cannot be undone.');
        }
    });

    // Delete Step - click handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.delete-step-btn');
        if (btn) {
            e.preventDefault();
            showDeleteConfirm('step', btn.dataset.stepId, 'Are you sure you want to delete this step and all its sections? This action cannot be undone.');
        }
    });

    // Confirm Delete - button handler
    document.getElementById('confirm-delete-btn')?.addEventListener('click', async () => {
        if (!deleteType || !deleteId) return;

        const endpoints = {
            question: `/api/v1/questionnaires/${questionnaireId}/questions/${deleteId}`,
            block: `/api/v1/questionnaires/${questionnaireId}/blocks/${deleteId}`,
            step: `/api/v1/questionnaires/${questionnaireId}/steps/${deleteId}`,
        };

        try {
            const response = await fetch(endpoints[deleteType], {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Failed to delete item');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to delete item. Please try again.');
        }

        closeModal('confirm-delete-modal');
    });

    // ==================== DRAG AND DROP ====================

    let draggedType = null;

    // Handle drag start on question type items
    document.querySelectorAll('.draggable-question-type').forEach(item => {
        item.addEventListener('dragstart', (e) => {
            draggedType = item.dataset.type;
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('text/plain', draggedType);

            // Add dragging class for visual feedback
            item.classList.add('opacity-50', 'ring-2', 'ring-primary');

            // Highlight all drop zones
            document.querySelectorAll('.question-drop-zone').forEach(zone => {
                zone.classList.add('border-2', 'border-dashed', 'border-primary/40');
            });
        });

        item.addEventListener('dragend', (e) => {
            // Remove dragging class
            item.classList.remove('opacity-50', 'ring-2', 'ring-primary');

            // Remove highlight from all drop zones
            document.querySelectorAll('.question-drop-zone').forEach(zone => {
                zone.classList.remove('border-2', 'border-dashed', 'border-primary/40', 'bg-primary/10', 'border-primary');
            });

            draggedType = null;
        });
    });

    // Handle drag over and drop on question drop zones
    document.addEventListener('dragover', (e) => {
        const dropZone = e.target.closest('.question-drop-zone');
        if (dropZone && draggedType) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        }
    });

    document.addEventListener('dragenter', (e) => {
        const dropZone = e.target.closest('.question-drop-zone');
        if (dropZone && draggedType) {
            e.preventDefault();
            dropZone.classList.add('bg-primary/10', 'border-primary');
            dropZone.classList.remove('border-primary/40');
        }
    });

    document.addEventListener('dragleave', (e) => {
        const dropZone = e.target.closest('.question-drop-zone');
        if (dropZone && draggedType) {
            // Check if we're leaving to a child element
            const relatedTarget = e.relatedTarget;
            if (!dropZone.contains(relatedTarget)) {
                dropZone.classList.remove('bg-primary/10', 'border-primary');
                dropZone.classList.add('border-primary/40');
            }
        }
    });

    document.addEventListener('drop', (e) => {
        const dropZone = e.target.closest('.question-drop-zone');
        if (dropZone && draggedType) {
            e.preventDefault();

            const blockId = dropZone.dataset.blockId;
            const droppedType = e.dataTransfer.getData('text/plain') || draggedType;

            // Remove visual feedback
            dropZone.classList.remove('bg-primary/10', 'border-primary', 'border-2', 'border-dashed', 'border-primary/40');

            // Reset form and select the dropped question type
            addQuestionForm.reset();
            clearQuestionTypeSelection();

            // Set the block ID
            document.getElementById('question-block-id').value = blockId;

            // Select the question type (pre-select it)
            selectQuestionType(droppedType);

            // Hide question type selector since type is pre-selected via drag
            questionTypeSelector.classList.add('hidden');

            // Show/hide options container based on type
            if (selectTypes.includes(droppedType)) {
                optionsContainer.classList.remove('hidden');
            } else {
                optionsContainer.classList.add('hidden');
            }

            // Open the modal
            openModal('add-question-modal');
        }
    });
});
</script>
@endpush
