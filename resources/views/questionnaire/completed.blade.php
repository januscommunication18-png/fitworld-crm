@extends('layouts.questionnaire', ['host' => $response->host])

@section('title', 'Thank You')

@section('content')
<div class="questionnaire-container">
    <div class="text-center py-12">
        {{-- Success Icon --}}
        <div class="mx-auto w-20 h-20 rounded-full bg-success/20 flex items-center justify-center mb-6">
            <span class="icon-[tabler--check] size-10 text-success"></span>
        </div>

        {{-- Thank You Message --}}
        <h2 class="text-2xl font-bold mb-3">Thank You!</h2>

        @if($questionnaire->thank_you_message)
            <p class="text-base-content/70 max-w-md mx-auto">
                {{ $questionnaire->thank_you_message }}
            </p>
        @else
            <p class="text-base-content/70 max-w-md mx-auto">
                Your responses have been submitted successfully. We appreciate you taking the time to complete this questionnaire.
            </p>
        @endif

        {{-- Completion Details --}}
        <div class="mt-8 p-4 bg-base-200 rounded-lg inline-block text-left">
            <div class="flex items-center gap-3 text-sm text-base-content/60">
                <span class="icon-[tabler--calendar] size-5"></span>
                <span>Completed on {{ $response->completed_at->format('F j, Y \a\t g:i A') }}</span>
            </div>
        </div>

        {{-- Studio Info --}}
        <div class="mt-8 pt-8 border-t border-base-300">
            <p class="text-sm text-base-content/50 mb-2">Questions? Contact us:</p>
            <p class="font-medium">{{ $response->host->studio_name }}</p>
            @if($response->host->email)
                <a href="mailto:{{ $response->host->email }}" class="text-primary hover:underline text-sm">
                    {{ $response->host->email }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
