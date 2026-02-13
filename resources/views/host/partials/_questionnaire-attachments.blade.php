@php
    $attachments = $attachments ?? collect();
    $questionnaires = $questionnaires ?? collect();
    $existingAttachments = $attachments->keyBy('questionnaire_id');
@endphp

<div class="card bg-base-100">
    <div class="card-header">
        <h3 class="card-title">
            <span class="icon-[tabler--forms] size-5 me-2"></span>
            Intake Questionnaires
        </h3>
    </div>
    <div class="card-body">
        @if($questionnaires->isEmpty())
            <div class="text-center py-4">
                <span class="icon-[tabler--forms] size-12 text-base-content/20 mx-auto block mb-2"></span>
                <p class="text-base-content/60 text-sm">No published questionnaires available.</p>
                <a href="{{ route('questionnaires.create') }}" class="link link-primary text-sm">Create one first</a>
            </div>
        @else
            <p class="text-sm text-base-content/60 mb-4">
                Attach questionnaires that clients must complete when booking. Configure when each should be collected.
            </p>

            <div class="space-y-3" id="questionnaire-attachments">
                @foreach($questionnaires as $questionnaire)
                    @php
                        $existing = $existingAttachments->get($questionnaire->id);
                        $isAttached = old("questionnaire_attachments.{$questionnaire->id}.attached", $existing ? true : false);
                    @endphp
                    <div class="border border-base-content/10 rounded-lg p-4 questionnaire-attachment-item {{ $isAttached ? 'bg-base-200/30' : '' }}"
                         data-questionnaire-id="{{ $questionnaire->id }}">
                        <div class="flex items-start gap-3">
                            <input type="checkbox"
                                   name="questionnaire_attachments[{{ $questionnaire->id }}][attached]"
                                   value="1"
                                   class="checkbox checkbox-primary mt-1 questionnaire-toggle"
                                   id="qa_{{ $questionnaire->id }}"
                                   {{ $isAttached ? 'checked' : '' }}>
                            <div class="flex-1">
                                <label for="qa_{{ $questionnaire->id }}" class="font-medium cursor-pointer">
                                    {{ $questionnaire->name }}
                                </label>
                                @if($questionnaire->intro_text)
                                    <p class="text-xs text-base-content/50 mt-1">{{ Str::limit($questionnaire->intro_text, 100) }}</p>
                                @endif

                                <div class="questionnaire-options mt-3 {{ $isAttached ? '' : 'hidden' }}">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="label-text text-xs" for="qa_timing_{{ $questionnaire->id }}">Collection Timing</label>
                                            <select name="questionnaire_attachments[{{ $questionnaire->id }}][collection_timing]"
                                                    id="qa_timing_{{ $questionnaire->id }}"
                                                    class="select select-sm w-full">
                                                @foreach(\App\Models\QuestionnaireAttachment::getCollectionTimings() as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ old("questionnaire_attachments.{$questionnaire->id}.collection_timing", $existing?->collection_timing ?? 'after_booking') === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-text text-xs" for="qa_applies_{{ $questionnaire->id }}">Applies To</label>
                                            <select name="questionnaire_attachments[{{ $questionnaire->id }}][applies_to]"
                                                    id="qa_applies_{{ $questionnaire->id }}"
                                                    class="select select-sm w-full">
                                                @foreach(\App\Models\QuestionnaireAttachment::getAppliesTo() as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ old("questionnaire_attachments.{$questionnaire->id}.applies_to", $existing?->applies_to ?? 'first_time_only') === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex items-end">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox"
                                                       name="questionnaire_attachments[{{ $questionnaire->id }}][is_required]"
                                                       value="1"
                                                       class="checkbox checkbox-sm checkbox-primary"
                                                       {{ old("questionnaire_attachments.{$questionnaire->id}.is_required", $existing?->is_required ?? true) ? 'checked' : '' }}>
                                                <span class="label-text text-xs">Required</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($questionnaire->isWizard())
                                    <span class="badge badge-sm badge-ghost">Wizard</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">Single Page</span>
                                @endif
                                @if($questionnaire->estimated_minutes)
                                    <span class="text-xs text-base-content/50">~{{ $questionnaire->estimated_minutes }} min</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-base-content/10">
                <p class="text-xs text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-4 align-middle me-1"></span>
                    <strong>Before booking (blocking):</strong> Client must complete before checkout.
                    <strong>After booking:</strong> Email sent after booking, tracked as intake pending.
                    <strong>Before first session:</strong> Reminder sent before their first session.
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.questionnaire-toggle').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const item = this.closest('.questionnaire-attachment-item');
            const options = item.querySelector('.questionnaire-options');

            if (this.checked) {
                item.classList.add('bg-base-200/30');
                options.classList.remove('hidden');
            } else {
                item.classList.remove('bg-base-200/30');
                options.classList.add('hidden');
            }
        });
    });
});
</script>
@endpush
