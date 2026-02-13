@extends('layouts.settings')

@section('title', 'Response from ' . ($response->client?->full_name ?? 'Anonymous') . ' — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.index') }}">Questionnaires</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('questionnaires.responses', $questionnaire) }}">Responses</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">View Response</li>
    </ol>
@endsection

@section('settings-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('questionnaires.responses', $questionnaire) }}" class="btn btn-ghost btn-sm btn-circle">
                <span class="icon-[tabler--arrow-left] size-5"></span>
            </a>
            <div>
                <h1 class="text-2xl font-bold">Response Details</h1>
                <p class="text-base-content/60 mt-1">{{ $questionnaire->name }}</p>
            </div>
        </div>
        <span class="badge {{ \App\Models\QuestionnaireResponse::getStatusBadgeClass($response->status) }} badge-lg">
            {{ \App\Models\QuestionnaireResponse::getStatuses()[$response->status] ?? $response->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content - Answers --}}
        <div class="lg:col-span-2 space-y-6">
            @php
                $answersById = $response->answers->keyBy('question_id');
            @endphp

            @if($questionnaire->isWizard())
                {{-- Wizard Layout --}}
                @foreach($response->version->steps as $step)
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">{{ $step->title }}</h3>
                            @if($step->description)
                                <p class="text-sm text-base-content/60">{{ $step->description }}</p>
                            @endif
                        </div>
                        <div class="card-body">
                            @foreach($step->blocks as $block)
                                @if($block->title)
                                    <div class="mb-4">
                                        <h4 class="font-semibold text-base-content/80">{{ $block->title }}</h4>
                                    </div>
                                @endif

                                <div class="space-y-4">
                                    @foreach($block->questions as $question)
                                        @include('host.questionnaires.partials.response-answer', [
                                            'question' => $question,
                                            'answer' => $answersById->get($question->id)
                                        ])
                                    @endforeach
                                </div>

                                @if(!$loop->last)
                                    <div class="divider"></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Single Page Layout --}}
                @foreach($response->version->blocks as $block)
                    <div class="card bg-base-100">
                        <div class="card-header">
                            <h3 class="card-title">{{ $block->title ?: 'Questions' }}</h3>
                            @if($block->description)
                                <p class="text-sm text-base-content/60">{{ $block->description }}</p>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="space-y-4">
                                @foreach($block->questions as $question)
                                    @include('host.questionnaires.partials.response-answer', [
                                        'question' => $question,
                                        'answer' => $answersById->get($question->id)
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Sidebar - Response Info --}}
        <div class="space-y-6">
            {{-- Client Info --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Client</h3>
                </div>
                <div class="card-body">
                    @if($response->client)
                        <div class="flex items-center gap-3">
                            <div class="avatar avatar-placeholder">
                                <div class="bg-primary text-primary-content w-12 h-12 rounded-full font-bold">
                                    {{ strtoupper(substr($response->client->first_name, 0, 1) . substr($response->client->last_name, 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <div class="font-medium">{{ $response->client->full_name }}</div>
                                <div class="text-sm text-base-content/60">{{ $response->client->email }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('clients.show', $response->client) }}" class="btn btn-sm btn-ghost w-full">
                                <span class="icon-[tabler--user] size-4"></span>
                                View Profile
                            </a>
                        </div>
                    @else
                        <p class="text-base-content/50">Anonymous response</p>
                    @endif
                </div>
            </div>

            {{-- Response Metadata --}}
            <div class="card bg-base-100">
                <div class="card-header">
                    <h3 class="card-title">Response Info</h3>
                </div>
                <div class="card-body space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Created</span>
                        <span>{{ $response->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if($response->started_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Started</span>
                            <span>{{ $response->started_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                    @if($response->completed_at)
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">Completed</span>
                            <span>{{ $response->completed_at->format('M j, Y g:i A') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-sm">
                        <span class="text-base-content/60">Version</span>
                        <span>v{{ $response->version->version_number }}</span>
                    </div>
                    @if($response->ip_address)
                        <div class="flex justify-between text-sm">
                            <span class="text-base-content/60">IP Address</span>
                            <span class="font-mono text-xs">{{ $response->ip_address }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Actions --}}
            @if(!$response->isCompleted())
                <div class="card bg-base-100">
                    <div class="card-body space-y-2">
                        <button type="button" class="btn btn-ghost btn-sm w-full" onclick="copyLink('{{ $response->getResponseUrl() }}')">
                            <span class="icon-[tabler--link] size-4"></span>
                            Copy Response Link
                        </button>
                        <form action="{{ route('questionnaires.responses.resend', [$questionnaire, $response]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm w-full">
                                <span class="icon-[tabler--refresh] size-4"></span>
                                Regenerate Link
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link copied to clipboard!');
    });
}
</script>
@endpush
@endsection
