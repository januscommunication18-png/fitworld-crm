@php
    $attachments = $attachments ?? collect();
    $progressTemplates = $progressTemplates ?? collect();
    $existingAttachments = $attachments->keyBy('progress_template_id');
@endphp

<div class="card bg-base-100">
    <div class="card-header">
        <h3 class="card-title">
            <span class="icon-[tabler--chart-line] size-5 me-2"></span>
            Progress Tracking
        </h3>
    </div>
    <div class="card-body">
        @if($progressTemplates->isEmpty())
            <div class="text-center py-4">
                <span class="icon-[tabler--chart-line] size-12 text-base-content/20 mx-auto block mb-2"></span>
                <p class="text-base-content/60 text-sm">No progress templates enabled.</p>
                <a href="{{ route('progress-templates.index') }}" class="link link-primary text-sm">Enable templates first</a>
            </div>
        @else
            <p class="text-sm text-base-content/60 mb-4">
                Attach progress templates to track client metrics for this class. Instructors can record progress for attendees.
            </p>

            <div class="space-y-3" id="progress-template-attachments">
                @foreach($progressTemplates as $template)
                    @php
                        $existing = $existingAttachments->get($template->id);
                        $isAttached = old("progress_template_attachments.{$template->id}.attached", $existing ? true : false);
                    @endphp
                    <div class="border border-base-content/10 rounded-lg p-4 progress-template-attachment-item {{ $isAttached ? 'bg-base-200/30' : '' }}"
                         data-template-id="{{ $template->id }}">
                        <div class="flex items-start gap-3">
                            <input type="checkbox"
                                   name="progress_template_attachments[{{ $template->id }}][attached]"
                                   value="1"
                                   class="checkbox checkbox-primary mt-1 progress-template-toggle"
                                   id="pta_{{ $template->id }}"
                                   {{ $isAttached ? 'checked' : '' }}>
                            <div class="flex-1">
                                <label for="pta_{{ $template->id }}" class="font-medium cursor-pointer flex items-center gap-2">
                                    <span class="icon-[tabler--{{ $template->icon ?? 'chart-line' }}] size-4 text-primary"></span>
                                    {{ $template->name }}
                                </label>
                                @if($template->description)
                                    <p class="text-xs text-base-content/50 mt-1">{{ Str::limit($template->description, 100) }}</p>
                                @endif

                                <div class="progress-template-options mt-3 {{ $isAttached ? '' : 'hidden' }}">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                        <div>
                                            <label class="label-text text-xs" for="pta_trigger_{{ $template->id }}">When to Record</label>
                                            <select name="progress_template_attachments[{{ $template->id }}][trigger_point]"
                                                    id="pta_trigger_{{ $template->id }}"
                                                    class="select select-sm w-full">
                                                @foreach(\App\Models\ProgressTemplateAttachment::getTriggerPointOptions() as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ old("progress_template_attachments.{$template->id}.trigger_point", $existing?->trigger_point ?? 'after_class') === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="label-text text-xs" for="pta_frequency_{{ $template->id }}">Tracking Frequency</label>
                                            <select name="progress_template_attachments[{{ $template->id }}][tracking_frequency]"
                                                    id="pta_frequency_{{ $template->id }}"
                                                    class="select select-sm w-full tracking-frequency-select"
                                                    data-template-id="{{ $template->id }}">
                                                @foreach(\App\Models\ProgressTemplateAttachment::getTrackingFrequencyOptions() as $value => $label)
                                                    <option value="{{ $value }}"
                                                        {{ old("progress_template_attachments.{$template->id}.tracking_frequency", $existing?->tracking_frequency ?? 'every_class') === $value ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div id="custom_interval_{{ $template->id }}" class="{{ old("progress_template_attachments.{$template->id}.tracking_frequency", $existing?->tracking_frequency ?? 'every_class') === 'custom' ? '' : 'hidden' }}">
                                            <label class="label-text text-xs" for="pta_interval_{{ $template->id }}">Every X Days</label>
                                            <input type="number"
                                                   name="progress_template_attachments[{{ $template->id }}][tracking_interval_days]"
                                                   id="pta_interval_{{ $template->id }}"
                                                   class="input input-sm w-full"
                                                   min="1"
                                                   max="365"
                                                   placeholder="e.g., 3"
                                                   value="{{ old("progress_template_attachments.{$template->id}.tracking_interval_days", $existing?->tracking_interval_days ?? '') }}">
                                        </div>
                                        <div class="flex items-end gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox"
                                                       name="progress_template_attachments[{{ $template->id }}][is_required]"
                                                       value="1"
                                                       class="checkbox checkbox-sm checkbox-primary"
                                                       {{ old("progress_template_attachments.{$template->id}.is_required", $existing?->is_required ?? false) ? 'checked' : '' }}>
                                                <span class="label-text text-xs">Required</span>
                                            </label>
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input type="checkbox"
                                                       name="progress_template_attachments[{{ $template->id }}][notify_instructor]"
                                                       value="1"
                                                       class="checkbox checkbox-sm checkbox-info"
                                                       {{ old("progress_template_attachments.{$template->id}.notify_instructor", $existing?->notify_instructor ?? true) ? 'checked' : '' }}>
                                                <span class="label-text text-xs">Notify</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-sm badge-ghost">{{ $template->sections->count() }} sections</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-base-content/10">
                <p class="text-xs text-base-content/50">
                    <span class="icon-[tabler--info-circle] size-4 align-middle me-1"></span>
                    <strong>After class:</strong> Recommended for most cases. Record progress after the session ends.
                    <strong>Before class:</strong> For initial assessments before the session.
                    <strong>Any time:</strong> Record whenever convenient.
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle options visibility when checkbox is changed
    document.querySelectorAll('.progress-template-toggle').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const item = this.closest('.progress-template-attachment-item');
            const options = item.querySelector('.progress-template-options');

            if (this.checked) {
                item.classList.add('bg-base-200/30');
                options.classList.remove('hidden');
            } else {
                item.classList.remove('bg-base-200/30');
                options.classList.add('hidden');
            }
        });
    });

    // Show/hide custom interval input based on frequency selection
    document.querySelectorAll('.tracking-frequency-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const templateId = this.dataset.templateId;
            const customIntervalDiv = document.getElementById('custom_interval_' + templateId);

            if (this.value === 'custom') {
                customIntervalDiv.classList.remove('hidden');
            } else {
                customIntervalDiv.classList.add('hidden');
            }
        });
    });
});
</script>
@endpush
