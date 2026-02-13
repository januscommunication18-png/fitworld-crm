<div class="flex items-start gap-3 p-3 bg-base-200/30 rounded-lg group" data-question-id="{{ $question->id }}">
    <span class="icon-[tabler--grip-vertical] size-4 text-base-content/20 cursor-move mt-1 opacity-0 group-hover:opacity-100 transition-opacity"></span>

    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="{{ \App\Models\QuestionnaireQuestion::getQuestionTypeIcon($question->question_type) }} size-4 text-primary"></span>
                <span class="font-medium text-sm">{{ $question->question_label }}</span>
                @if($question->is_required)
                    <span class="text-error text-xs">*</span>
                @endif
            </div>
            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button type="button" class="btn btn-ghost btn-xs btn-square edit-question-btn" data-question-id="{{ $question->id }}" title="Edit">
                    <span class="icon-[tabler--edit] size-3.5"></span>
                </button>
                <button type="button" class="btn btn-ghost btn-xs btn-square text-error delete-question-btn" data-question-id="{{ $question->id }}" title="Delete">
                    <span class="icon-[tabler--trash] size-3.5"></span>
                </button>
            </div>
        </div>

        <div class="flex items-center gap-2 mt-1 text-xs text-base-content/50">
            <span>{{ \App\Models\QuestionnaireQuestion::getQuestionTypes()[$question->question_type] }}</span>
            @if($question->is_sensitive)
                <span class="badge badge-warning badge-xs">Sensitive</span>
            @endif
            @if($question->isInstructorOnly())
                <span class="badge badge-info badge-xs">Instructor Only</span>
            @endif
        </div>

        @if($question->help_text)
            <p class="text-xs text-base-content/40 mt-1 truncate">{{ $question->help_text }}</p>
        @endif

        @if($question->hasOptions() && $question->options)
            <div class="flex flex-wrap gap-1 mt-2">
                @foreach(array_slice($question->options, 0, 4) as $option)
                    <span class="badge badge-ghost badge-xs">{{ $option['label'] ?? $option }}</span>
                @endforeach
                @if(count($question->options) > 4)
                    <span class="badge badge-ghost badge-xs">+{{ count($question->options) - 4 }} more</span>
                @endif
            </div>
        @endif
    </div>
</div>
