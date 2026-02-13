@php
    $hasAnswer = $answer && $answer->answer !== null && $answer->answer !== '';
    $isSensitive = $question->is_sensitive;
    $isInstructorOnly = $question->isInstructorOnly();
@endphp

<div class="border-b border-base-200 pb-4 last:border-0 last:pb-0">
    <div class="flex items-start justify-between gap-2 mb-2">
        <div class="flex items-center gap-2">
            <span class="{{ \App\Models\QuestionnaireQuestion::getQuestionTypeIcon($question->question_type) }} size-4 text-base-content/40"></span>
            <span class="font-medium text-sm">{{ $question->question_label }}</span>
            @if($question->is_required)
                <span class="text-error text-xs">*</span>
            @endif
        </div>
        <div class="flex items-center gap-1">
            @if($isSensitive)
                <span class="badge badge-warning badge-xs">Sensitive</span>
            @endif
            @if($isInstructorOnly)
                <span class="badge badge-info badge-xs">Instructor Only</span>
            @endif
        </div>
    </div>

    <div class="pl-6">
        @if($hasAnswer)
            @if($isSensitive)
                {{-- Sensitive answer with reveal option --}}
                <div x-data="{ revealed: false }">
                    <div x-show="!revealed" class="flex items-center gap-2">
                        <span class="text-base-content/40 italic">Hidden for privacy</span>
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
