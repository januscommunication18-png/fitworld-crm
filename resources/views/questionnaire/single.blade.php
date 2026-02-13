@extends('layouts.questionnaire', ['host' => $response->host])

@section('title', $questionnaire->name)

@section('content')
<div class="questionnaire-container">
    {{-- Questionnaire Header --}}
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold">{{ $questionnaire->name }}</h2>
        @if($questionnaire->intro_text)
            <p class="text-base-content/70 mt-2">{{ $questionnaire->intro_text }}</p>
        @endif
        @if($questionnaire->estimated_minutes)
            <p class="text-sm text-base-content/50 mt-2">
                <span class="icon-[tabler--clock] size-4 inline-block align-middle me-1"></span>
                Estimated time: {{ $questionnaire->estimated_minutes }} minutes
            </p>
        @endif
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

    <form action="{{ route('questionnaire.store', $response->token) }}" method="POST" id="questionnaire-form">
        @csrf

        {{-- Blocks --}}
        @foreach($version->blocks as $block)
            <div class="mb-8">
                {{-- Block Header --}}
                @if($block->title)
                    <div class="mb-4 {{ $block->isCardStyle() ? 'bg-base-100 p-4 rounded-lg border border-base-300' : '' }}">
                        <h3 class="text-lg font-semibold">{{ $block->title }}</h3>
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

        {{-- Submit Button --}}
        <div class="sticky-bottom">
            <button type="submit" class="btn btn-primary btn-lg w-full tap-target">
                <span class="icon-[tabler--check] size-5"></span>
                Submit
            </button>
        </div>
    </form>
</div>
@endsection
