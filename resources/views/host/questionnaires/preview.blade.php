@extends('layouts.settings')

@section('title', 'Preview: ' . $questionnaire->name . ' — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Preview</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold">Preview</h1>
                    <span class="badge badge-info badge-sm">Preview Mode</span>
                </div>
                <p class="text-base-content/60 mt-1">{{ $questionnaire->name }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            {{-- Device Toggle --}}
            <div class="tabs tabs-boxed tabs-sm bg-base-200">
                <button class="tab tab-active" data-device="desktop" onclick="setPreviewDevice('desktop')">
                    <span class="icon-[tabler--device-desktop] size-4 me-1"></span>
                    Desktop
                </button>
                <button class="tab" data-device="mobile" onclick="setPreviewDevice('mobile')">
                    <span class="icon-[tabler--device-mobile] size-4 me-1"></span>
                    Mobile
                </button>
            </div>
            <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-primary btn-sm">
                <span class="icon-[tabler--edit] size-4"></span>
                Edit
            </a>
        </div>
    </div>

    {{-- Preview Container --}}
    <div class="flex justify-center">
        <div id="preview-container" class="w-full max-w-3xl transition-all duration-300">
            {{-- Preview Frame --}}
            <div class="bg-base-300/50 rounded-2xl p-4 sm:p-6">
                <div class="bg-base-100 rounded-xl shadow-2xl overflow-hidden">
                    {{-- Questionnaire Header --}}
                    <div class="bg-gradient-to-r from-primary to-primary/80 text-primary-content px-6 py-8">
                        <div class="max-w-xl mx-auto text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-white/20 mb-4">
                                <span class="icon-[tabler--clipboard-list] size-8"></span>
                            </div>
                            <h2 class="text-2xl font-bold">{{ $questionnaire->name }}</h2>
                            @if($questionnaire->intro_text)
                                <p class="mt-3 opacity-90">{{ $questionnaire->intro_text }}</p>
                            @endif
                            @if($questionnaire->estimated_minutes)
                                <div class="mt-4 inline-flex items-center gap-2 bg-white/10 rounded-full px-4 py-2 text-sm">
                                    <span class="icon-[tabler--clock] size-4"></span>
                                    <span>Estimated time: {{ $questionnaire->estimated_minutes }} minutes</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="p-6 sm:p-8">
                        @if($questionnaire->isWizard())
                            {{-- Wizard Mode --}}
                            @if($version->steps->count() > 0)
                                @php $totalSteps = $version->steps->count(); @endphp

                                {{-- Progress Steps --}}
                                <div class="mb-8" id="wizard-progress">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-base-content/70" id="step-counter">Step 1 of {{ $totalSteps }}</span>
                                        <span class="text-sm text-base-content/50" id="progress-percent">{{ round(1 / $totalSteps * 100) }}% complete</span>
                                    </div>
                                    <div class="w-full bg-base-200 rounded-full h-2">
                                        <div class="bg-primary h-2 rounded-full transition-all duration-300" id="progress-bar" style="width: {{ round(1 / $totalSteps * 100) }}%"></div>
                                    </div>
                                </div>

                                {{-- Step Indicator Pills --}}
                                <div class="flex flex-wrap gap-2 mb-8" id="step-pills">
                                    @foreach($version->steps as $index => $step)
                                        <button type="button"
                                                class="step-pill flex items-center gap-2 px-3 py-1.5 rounded-full text-sm transition-all {{ $index === 0 ? 'bg-primary text-primary-content' : 'bg-base-200 text-base-content/60 hover:bg-base-300' }}"
                                                data-step="{{ $index }}"
                                                onclick="goToStep({{ $index }})">
                                            <span class="step-pill-number w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $index === 0 ? 'bg-white/20' : 'bg-base-300' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="font-medium">{{ Str::limit($step->title, 20) }}</span>
                                            <span class="step-pill-indicator icon-[tabler--circle-dot] size-3 {{ $index === 0 ? '' : 'hidden' }}"></span>
                                        </button>
                                    @endforeach
                                </div>

                                {{-- All Steps Content --}}
                                <div id="wizard-steps-container">
                                    @foreach($version->steps as $stepIndex => $step)
                                        <div class="wizard-step {{ $stepIndex === 0 ? '' : 'hidden' }}" data-step="{{ $stepIndex }}">
                                            <div class="space-y-6">
                                                {{-- Step Header --}}
                                                <div class="border-b border-base-200 pb-4">
                                                    <h3 class="text-xl font-bold text-base-content">{{ $step->title }}</h3>
                                                    @if($step->description)
                                                        <p class="text-base-content/60 mt-1">{{ $step->description }}</p>
                                                    @endif
                                                </div>

                                                {{-- Blocks --}}
                                                @forelse($step->blocks as $block)
                                                    @include('host.questionnaires.partials.preview-block', ['block' => $block])
                                                @empty
                                                    <div class="text-center py-8 text-base-content/50">
                                                        <span class="icon-[tabler--info-circle] size-6 mx-auto mb-2 block"></span>
                                                        <p>No questions in this step yet.</p>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- Navigation --}}
                                <div class="flex justify-between items-center mt-10 pt-6 border-t border-base-200">
                                    <button type="button" class="btn btn-ghost gap-2" id="prev-btn" onclick="prevStep()" disabled>
                                        <span class="icon-[tabler--arrow-left] size-5"></span>
                                        Back
                                    </button>
                                    <div class="text-sm text-base-content/50">
                                        Press <kbd class="kbd kbd-sm">Enter</kbd> to continue
                                    </div>
                                    <button type="button" class="btn btn-primary gap-2" id="next-btn" onclick="nextStep()">
                                        <span id="next-btn-text">Next</span>
                                        <span class="icon-[tabler--arrow-right] size-5" id="next-btn-icon"></span>
                                    </button>
                                </div>

                                {{-- Completion Screen (hidden initially) --}}
                                <div id="wizard-complete" class="hidden">
                                    <div class="text-center py-12">
                                        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-success/10 mb-4">
                                            <span class="icon-[tabler--check] size-10 text-success"></span>
                                        </div>
                                        <h3 class="text-xl font-bold mb-2">All Done!</h3>
                                        <p class="text-base-content/60 mb-6">You've completed all steps of this questionnaire.</p>
                                        <button type="button" class="btn btn-primary btn-wide" onclick="submitWizard()">
                                            <span class="icon-[tabler--send] size-5"></span>
                                            Submit Questionnaire
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Empty State --}}
                                <div class="text-center py-16">
                                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-base-200 mb-4">
                                        <span class="icon-[tabler--list-numbers] size-10 text-base-content/30"></span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-base-content/70">No Steps Added</h3>
                                    <p class="text-base-content/50 mt-2 max-w-sm mx-auto">Add steps to your wizard questionnaire to see the preview.</p>
                                    <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-primary btn-sm mt-4">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Steps
                                    </a>
                                </div>
                            @endif
                        @else
                            {{-- Single Page Mode --}}
                            @if($version->blocks->count() > 0)
                                @php
                                    $totalQuestions = $version->blocks->sum(fn($b) => $b->questions->count());
                                    $requiredQuestions = $version->blocks->sum(fn($b) => $b->questions->where('is_required', true)->count());
                                @endphp

                                {{-- Stats Bar --}}
                                <div class="flex items-center justify-between text-sm text-base-content/60 mb-6 pb-4 border-b border-base-200">
                                    <div class="flex items-center gap-4">
                                        <span class="flex items-center gap-1.5">
                                            <span class="icon-[tabler--help-circle] size-4"></span>
                                            {{ $totalQuestions }} {{ Str::plural('question', $totalQuestions) }}
                                        </span>
                                        @if($requiredQuestions > 0)
                                            <span class="flex items-center gap-1.5">
                                                <span class="icon-[tabler--asterisk] size-4 text-error"></span>
                                                {{ $requiredQuestions }} required
                                            </span>
                                        @endif
                                    </div>
                                    <span class="flex items-center gap-1.5">
                                        <span class="icon-[tabler--forms] size-4"></span>
                                        {{ $version->blocks->count() }} {{ Str::plural('section', $version->blocks->count()) }}
                                    </span>
                                </div>

                                {{-- Blocks --}}
                                <div class="space-y-8">
                                    @foreach($version->blocks as $index => $block)
                                        @include('host.questionnaires.partials.preview-block', ['block' => $block, 'blockIndex' => $index])
                                    @endforeach
                                </div>

                                {{-- Submit Section --}}
                                <div class="mt-10 pt-6 border-t border-base-200">
                                    <div class="bg-base-200/50 rounded-xl p-6 text-center">
                                        <span class="icon-[tabler--check-circle] size-10 text-success mb-3 mx-auto block"></span>
                                        <h4 class="font-semibold mb-1">Ready to Submit?</h4>
                                        <p class="text-sm text-base-content/60 mb-4">Make sure you've answered all required questions.</p>
                                        <button class="btn btn-primary btn-wide">
                                            <span class="icon-[tabler--send] size-5"></span>
                                            Submit Questionnaire
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- Empty State --}}
                                <div class="text-center py-16">
                                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-base-200 mb-4">
                                        <span class="icon-[tabler--forms] size-10 text-base-content/30"></span>
                                    </div>
                                    <h3 class="text-lg font-semibold text-base-content/70">No Questions Added</h3>
                                    <p class="text-base-content/50 mt-2 max-w-sm mx-auto">Add questions to your questionnaire to see the preview.</p>
                                    <a href="{{ route('questionnaires.builder', $questionnaire) }}" class="btn btn-primary btn-sm mt-4">
                                        <span class="icon-[tabler--plus] size-4"></span>
                                        Add Questions
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="bg-base-200/30 px-6 py-4 text-center text-sm text-base-content/50">
                        <span class="icon-[tabler--lock] size-4 inline-block align-middle me-1"></span>
                        Your responses are secure and confidential
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setPreviewDevice(device) {
    const container = document.getElementById('preview-container');
    const tabs = document.querySelectorAll('[data-device]');

    tabs.forEach(tab => {
        tab.classList.remove('tab-active');
        if (tab.dataset.device === device) {
            tab.classList.add('tab-active');
        }
    });

    if (device === 'mobile') {
        container.classList.remove('max-w-3xl');
        container.classList.add('max-w-sm');
    } else {
        container.classList.remove('max-w-sm');
        container.classList.add('max-w-3xl');
    }
}

// Wizard Navigation
@if($questionnaire->isWizard() && $version->steps->count() > 0)
const totalSteps = {{ $version->steps->count() }};
let currentStep = 0;

function updateWizardUI() {
    // Update step content visibility
    document.querySelectorAll('.wizard-step').forEach((step, index) => {
        step.classList.toggle('hidden', index !== currentStep);
    });

    // Update progress bar
    const progress = Math.round(((currentStep + 1) / totalSteps) * 100);
    document.getElementById('progress-bar').style.width = progress + '%';
    document.getElementById('step-counter').textContent = `Step ${currentStep + 1} of ${totalSteps}`;
    document.getElementById('progress-percent').textContent = progress + '% complete';

    // Update step pills
    document.querySelectorAll('.step-pill').forEach((pill, index) => {
        const isActive = index === currentStep;
        const isCompleted = index < currentStep;

        // Reset classes
        pill.classList.remove('bg-primary', 'text-primary-content', 'bg-base-200', 'text-base-content/60', 'bg-success', 'text-success-content');

        if (isActive) {
            pill.classList.add('bg-primary', 'text-primary-content');
        } else if (isCompleted) {
            pill.classList.add('bg-success/20', 'text-success');
        } else {
            pill.classList.add('bg-base-200', 'text-base-content/60');
        }

        // Update number badge
        const numberBadge = pill.querySelector('.step-pill-number');
        numberBadge.classList.remove('bg-white/20', 'bg-base-300', 'bg-success');
        if (isActive) {
            numberBadge.classList.add('bg-white/20');
        } else if (isCompleted) {
            numberBadge.innerHTML = '<span class="icon-[tabler--check] size-3"></span>';
            numberBadge.classList.add('bg-success', 'text-white');
        } else {
            numberBadge.classList.add('bg-base-300');
            numberBadge.textContent = index + 1;
        }

        // Update indicator dot
        const indicator = pill.querySelector('.step-pill-indicator');
        indicator.classList.toggle('hidden', !isActive);
    });

    // Update navigation buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const nextBtnText = document.getElementById('next-btn-text');
    const nextBtnIcon = document.getElementById('next-btn-icon');

    prevBtn.disabled = currentStep === 0;

    if (currentStep === totalSteps - 1) {
        nextBtnText.textContent = 'Complete';
        nextBtnIcon.className = 'icon-[tabler--check] size-5';
    } else {
        nextBtnText.textContent = 'Next';
        nextBtnIcon.className = 'icon-[tabler--arrow-right] size-5';
    }

    // Scroll to top of content
    document.getElementById('wizard-steps-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function nextStep() {
    if (currentStep < totalSteps - 1) {
        currentStep++;
        updateWizardUI();
    } else {
        // Show completion screen
        document.getElementById('wizard-steps-container').classList.add('hidden');
        document.getElementById('wizard-progress').classList.add('hidden');
        document.getElementById('step-pills').classList.add('hidden');
        document.querySelector('.flex.justify-between.items-center.mt-10').classList.add('hidden');
        document.getElementById('wizard-complete').classList.remove('hidden');
    }
}

function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        updateWizardUI();
    }
}

function goToStep(stepIndex) {
    if (stepIndex >= 0 && stepIndex < totalSteps) {
        currentStep = stepIndex;
        updateWizardUI();
    }
}

function submitWizard() {
    alert('This is preview mode. In the actual form, responses would be submitted here.');
}

// Handle Enter key to go to next step
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.target.matches('textarea')) {
        e.preventDefault();
        nextStep();
    }
});
@endif
</script>
@endsection
