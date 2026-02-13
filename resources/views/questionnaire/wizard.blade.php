@extends('layouts.questionnaire', ['host' => $response->host])

@section('title', $questionnaire->name)

@section('content')
<div class="questionnaire-container">
    {{-- Questionnaire Header --}}
    <div class="text-center mb-4">
        <h2 class="text-2xl font-bold">{{ $questionnaire->name }}</h2>
        @if($questionnaire->intro_text && $currentStep === 0)
            <p class="text-base-content/70 mt-2">{{ $questionnaire->intro_text }}</p>
        @endif
    </div>

    {{-- Progress Bar --}}
    @php
        $totalSteps = $version->steps->count();
        $progressPercent = $totalSteps > 0 ? (($currentStep + 1) / $totalSteps) * 100 : 0;
    @endphp
    <div class="mb-6">
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-medium">Step {{ $currentStep + 1 }} of {{ $totalSteps }}</span>
            <span class="text-sm text-base-content/60">{{ round($progressPercent) }}% complete</span>
        </div>
        <div class="w-full bg-base-300 rounded-full h-2">
            <div class="bg-primary h-2 rounded-full progress-fill" style="width: {{ $progressPercent }}%"></div>
        </div>
    </div>

    {{-- Error Summary --}}
    @if($errors->any())
        <div class="alert alert-error mb-6">
            <span class="icon-[tabler--alert-circle] size-5"></span>
            <div>
                <h3 class="font-bold">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-sm mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Step Content --}}
    @foreach($version->steps as $stepIndex => $step)
        <div class="step-content {{ $stepIndex === $currentStep ? '' : 'hidden' }}" data-step="{{ $stepIndex }}">
            {{-- Step Header --}}
            <div class="mb-6">
                <h3 class="text-xl font-semibold">{{ $step->title }}</h3>
                @if($step->description)
                    <p class="text-base-content/60 mt-1">{{ $step->description }}</p>
                @endif
            </div>

            <form class="step-form" data-step="{{ $stepIndex }}">
                @csrf
                {{-- Blocks in this step --}}
                @foreach($step->blocks as $block)
                    <div class="mb-6">
                        {{-- Block Header --}}
                        @if($block->title)
                            <div class="mb-4 {{ $block->isCardStyle() ? 'bg-base-100 p-4 rounded-lg border border-base-300' : '' }}">
                                <h4 class="font-semibold">{{ $block->title }}</h4>
                                @if($block->description)
                                    <p class="text-sm text-base-content/60 mt-1">{{ $block->description }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Questions --}}
                        <div class="space-y-4">
                            @foreach($block->questions as $question)
                                @if($question->visibility !== 'instructor_only')
                                    @include('questionnaire.partials._question-input', [
                                        'question' => $question,
                                        'existingAnswers' => $existingAnswers
                                    ])
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </form>
        </div>
    @endforeach

    {{-- Navigation --}}
    <div class="sticky-bottom">
        <div class="flex gap-3">
            <button type="button"
                    id="btn-back"
                    class="btn btn-ghost tap-target {{ $currentStep === 0 ? 'invisible' : '' }}"
                    onclick="navigateStep(-1)">
                <span class="icon-[tabler--arrow-left] size-5"></span>
                Back
            </button>
            <button type="button"
                    id="btn-next"
                    class="btn btn-primary flex-1 tap-target {{ $currentStep === $totalSteps - 1 ? 'hidden' : '' }}"
                    onclick="navigateStep(1)">
                Next
                <span class="icon-[tabler--arrow-right] size-5"></span>
            </button>
            <form action="{{ route('questionnaire.complete', $response->token) }}" method="POST" id="complete-form" class="{{ $currentStep === $totalSteps - 1 ? 'flex-1' : 'hidden' }}">
                @csrf
                <button type="submit" class="btn btn-primary w-full tap-target">
                    <span class="icon-[tabler--check] size-5"></span>
                    Submit
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStep = {{ $currentStep }};
const totalSteps = {{ $totalSteps }};
const token = '{{ $response->token }}';

function navigateStep(direction) {
    const newStep = currentStep + direction;

    if (newStep < 0 || newStep >= totalSteps) {
        return;
    }

    // Save current step before moving
    saveCurrentStep().then(() => {
        // Hide current step
        document.querySelector(`[data-step="${currentStep}"]`).classList.add('hidden');

        // Show new step
        currentStep = newStep;
        document.querySelector(`[data-step="${currentStep}"]`).classList.remove('hidden');

        // Update UI
        updateNavigationUI();
        updateProgressBar();

        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

function saveCurrentStep() {
    const form = document.querySelector(`.step-form[data-step="${currentStep}"]`);
    const formData = new FormData(form);

    // Convert FormData to object
    const answers = {};
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('q_')) {
            // Handle multiple values (checkboxes)
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!answers[cleanKey]) {
                    answers[cleanKey] = [];
                }
                answers[cleanKey].push(value);
            } else {
                answers[key] = value;
            }
        }
    }

    return fetch(`/q/${token}/step`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            step_index: currentStep,
            answers: answers
        })
    }).then(response => response.json());
}

function updateNavigationUI() {
    const btnBack = document.getElementById('btn-back');
    const btnNext = document.getElementById('btn-next');
    const completeForm = document.getElementById('complete-form');

    // Back button visibility
    if (currentStep === 0) {
        btnBack.classList.add('invisible');
    } else {
        btnBack.classList.remove('invisible');
    }

    // Next/Submit button
    if (currentStep === totalSteps - 1) {
        btnNext.classList.add('hidden');
        completeForm.classList.remove('hidden');
        completeForm.classList.add('flex-1');
    } else {
        btnNext.classList.remove('hidden');
        completeForm.classList.add('hidden');
        completeForm.classList.remove('flex-1');
    }
}

function updateProgressBar() {
    const progressPercent = ((currentStep + 1) / totalSteps) * 100;
    document.querySelector('.progress-fill').style.width = `${progressPercent}%`;
    document.querySelector('.text-sm.font-medium').textContent = `Step ${currentStep + 1} of ${totalSteps}`;
    document.querySelector('.text-sm.text-base-content\\/60').textContent = `${Math.round(progressPercent)}% complete`;
}

// Save on form field change (auto-save)
document.querySelectorAll('.step-form input, .step-form select, .step-form textarea').forEach(el => {
    el.addEventListener('change', () => {
        saveCurrentStep();
    });
});

// Before submit, save current step
document.getElementById('complete-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Collect all answers from all forms
    document.querySelectorAll('.step-form').forEach(form => {
        const formData = new FormData(form);
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('q_') && key !== '_token') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                this.appendChild(input);
            }
        }
    });

    this.submit();
});
</script>
@endpush
@endsection
