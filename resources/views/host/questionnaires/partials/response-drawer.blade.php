{{-- Questionnaire Response Drawer --}}
{{-- Usage: @include('host.questionnaires.partials.response-drawer', ['response' => $response]) --}}

@php
    $questionnaire = $response->version->questionnaire;
    $statuses = \App\Models\QuestionnaireResponse::getStatuses();
@endphp

<x-detail-drawer id="response-{{ $response->id }}" title="{{ $questionnaire->name ?? 'Questionnaire Response' }}" size="4xl">
    {{-- Status Hero Section --}}
    @php
        $statusColors = [
            'pending' => 'from-warning/10 to-warning/5',
            'in_progress' => 'from-info/10 to-info/5',
            'completed' => 'from-success/10 to-success/5',
            'expired' => 'from-error/10 to-error/5',
        ];
        $statusIconColors = [
            'pending' => 'bg-warning/20 text-warning',
            'in_progress' => 'bg-info/20 text-info',
            'completed' => 'bg-success/20 text-success',
            'expired' => 'bg-error/20 text-error',
        ];
        $statusIcons = [
            'pending' => 'clock',
            'in_progress' => 'loader',
            'completed' => 'check',
            'expired' => 'x',
        ];
    @endphp
    <div class="bg-gradient-to-r {{ $statusColors[$response->status] ?? 'from-primary/10 to-primary/5' }} rounded-xl p-4 mb-5 -mt-1">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full {{ $statusIconColors[$response->status] ?? 'bg-primary/20 text-primary' }} flex items-center justify-center">
                    <span class="icon-[tabler--{{ $statusIcons[$response->status] ?? 'file-text' }}] size-6"></span>
                </div>
                <div>
                    <div class="text-xs text-base-content/60 uppercase tracking-wide">Status</div>
                    <span class="badge {{ \App\Models\QuestionnaireResponse::getStatusBadgeClass($response->status) }} mt-1">
                        {{ $statuses[$response->status] ?? $response->status }}
                    </span>
                </div>
            </div>
            @if($response->isCompleted())
                <div class="flex items-center gap-2 bg-success/20 text-success px-3 py-2 rounded-lg">
                    <span class="icon-[tabler--circle-check-filled] size-5"></span>
                    <span class="font-medium text-sm">Completed</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Client Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--user] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Client</h4>
        </div>
        @if($response->client)
            <div class="flex items-center gap-4">
                <x-avatar :src="$response->client->avatar_url ?? null" :initials="$response->client->initials" :alt="$response->client->full_name" size="lg" />
                <div class="flex-1">
                    <div class="font-semibold text-lg">{{ $response->client->full_name }}</div>
                    @if($response->client->email)
                        <div class="flex items-center gap-2 text-sm text-base-content/70 mt-1">
                            <span class="icon-[tabler--mail] size-4"></span>
                            {{ $response->client->email }}
                        </div>
                    @endif
                </div>
                <a href="{{ route('clients.show', $response->client) }}" class="btn btn-ghost btn-sm btn-circle" title="View Client">
                    <span class="icon-[tabler--chevron-right] size-5"></span>
                </a>
            </div>
        @else
            <div class="flex items-center gap-3 text-base-content/50">
                <span class="icon-[tabler--user-off] size-8"></span>
                <span>Anonymous Response</span>
            </div>
        @endif
    </div>

    {{-- Response Info Card --}}
    <div class="bg-base-200/50 rounded-xl p-4 mb-4">
        <div class="flex items-center gap-2 mb-3">
            <span class="icon-[tabler--info-circle] size-4 text-primary"></span>
            <h4 class="text-sm font-semibold uppercase tracking-wide">Response Info</h4>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-base-100 rounded-lg p-3">
                <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                    <span class="icon-[tabler--calendar-plus] size-3.5"></span>
                    Sent
                </div>
                <div class="font-medium text-sm">{{ $response->created_at->format('M j, Y') }}</div>
                <div class="text-xs text-base-content/60">{{ $response->created_at->format('g:i A') }}</div>
            </div>
            @if($response->completed_at)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--calendar-check] size-3.5"></span>
                        Completed
                    </div>
                    <div class="font-medium text-sm text-success">{{ $response->completed_at->format('M j, Y') }}</div>
                    <div class="text-xs text-success/70">{{ $response->completed_at->format('g:i A') }}</div>
                </div>
            @elseif($response->started_at)
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--calendar-event] size-3.5"></span>
                        Started
                    </div>
                    <div class="font-medium text-sm">{{ $response->started_at->format('M j, Y') }}</div>
                    <div class="text-xs text-base-content/60">{{ $response->started_at->format('g:i A') }}</div>
                </div>
            @else
                <div class="bg-base-100 rounded-lg p-3">
                    <div class="flex items-center gap-2 text-xs text-base-content/60 mb-1">
                        <span class="icon-[tabler--clock] size-3.5"></span>
                        Status
                    </div>
                    <div class="font-medium text-sm text-warning">Not Started</div>
                    <div class="text-xs text-base-content/60">Awaiting response</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Answers Section --}}
    @if($response->isCompleted() && $response->answers && $response->answers->count() > 0)
        <div class="bg-base-200/50 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-4">
                <span class="icon-[tabler--list-check] size-4 text-primary"></span>
                <h4 class="text-sm font-semibold uppercase tracking-wide">Responses</h4>
            </div>

            @php
                $answersById = $response->answers->keyBy('question_id');
                $blocks = $response->version->blocks ?? collect();
            @endphp

            <div class="space-y-4">
                @foreach($blocks as $block)
                    @if($block->title)
                        <div class="border-b border-base-300 pb-2 mb-3">
                            <h5 class="font-semibold text-base-content/80">{{ $block->title }}</h5>
                        </div>
                    @endif

                    @foreach($block->questions as $question)
                        @php
                            $answer = $answersById->get($question->id);
                            $hasAnswer = $answer && $answer->answer !== null && $answer->answer !== '';
                        @endphp
                        <div class="bg-base-100 rounded-lg p-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="{{ \App\Models\QuestionnaireQuestion::getQuestionTypeIcon($question->question_type) }} size-4 text-base-content/40"></span>
                                    <span class="font-medium text-sm">{{ $question->question_label }}</span>
                                    @if($question->is_required)
                                        <span class="text-error text-xs">*</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pl-6">
                                @if($hasAnswer)
                                    @if($question->is_sensitive)
                                        <div x-data="{ revealed: false }">
                                            <div x-show="!revealed" class="flex items-center gap-2">
                                                <span class="text-base-content/40 italic text-sm">Hidden for privacy</span>
                                                <button type="button" @click="revealed = true" class="btn btn-xs btn-ghost">
                                                    <span class="icon-[tabler--eye] size-3"></span>
                                                    Reveal
                                                </button>
                                            </div>
                                            <div x-show="revealed" x-cloak>
                                                @include('host.questionnaires.partials.answer-value', ['answer' => $answer, 'question' => $question])
                                                <button type="button" @click="revealed = false" class="btn btn-xs btn-ghost mt-1">
                                                    <span class="icon-[tabler--eye-off] size-3"></span>
                                                    Hide
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        @include('host.questionnaires.partials.answer-value', ['answer' => $answer, 'question' => $question])
                                    @endif
                                @else
                                    <span class="text-base-content/40 italic text-sm">No answer provided</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @elseif(!$response->isCompleted())
        <div class="bg-base-200/50 rounded-xl p-6 text-center">
            <span class="icon-[tabler--clock] size-12 text-warning/50 mx-auto block mb-3"></span>
            <p class="text-base-content/60">Waiting for client to complete the questionnaire.</p>
        </div>
    @endif

    <x-slot name="footer">
        <div class="flex items-center gap-2">
            @if(!$response->isCompleted() && method_exists($response, 'getResponseUrl'))
                <button type="button" class="btn btn-soft btn-primary" onclick="navigator.clipboard.writeText('{{ $response->getResponseUrl() }}').then(() => alert('Link copied!'))">
                    <span class="icon-[tabler--link] size-4 me-1"></span>
                    Copy Link
                </button>
            @endif
        </div>
        <a href="{{ route('questionnaires.responses.show', [$questionnaire, $response]) }}" class="btn btn-primary">
            <span class="icon-[tabler--external-link] size-4 me-1"></span>
            View Full Response
        </a>
    </x-slot>
</x-detail-drawer>
