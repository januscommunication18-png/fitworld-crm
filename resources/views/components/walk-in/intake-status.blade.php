{{-- Intake/Questionnaire Status Component --}}
<div class="space-y-3">
    <div class="flex items-center gap-2">
        <span class="icon-[tabler--clipboard-check] size-5 text-info"></span>
        <span class="font-medium">Intake Questionnaire</span>
        <span id="intake-required-badge" class="badge badge-warning badge-sm hidden">Required</span>
        <span id="intake-optional-badge" class="badge badge-ghost badge-sm hidden">Optional</span>
    </div>

    {{-- Questionnaire Name --}}
    <div id="intake-questionnaire-info" class="text-sm text-base-content/60 hidden">
        Questionnaire: <span id="intake-questionnaire-name" class="font-medium">---</span>
    </div>

    {{-- Intake Options --}}
    <div id="intake-options" class="space-y-2">
        {{-- Send Link Option --}}
        <label class="flex items-start gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
            <input type="radio" name="intake_option" value="send_link" class="radio radio-sm radio-primary mt-0.5" onchange="selectIntakeOption('send_link')" checked>
            <div>
                <div class="font-medium text-sm">Send intake link by email</div>
                <div class="text-xs text-base-content/60">Client will receive an email with the questionnaire link</div>
            </div>
        </label>

        {{-- Mark Complete Option (admin only) --}}
        <label id="intake-mark-complete-option" class="flex items-start gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
            <input type="radio" name="intake_option" value="mark_complete" class="radio radio-sm radio-primary mt-0.5" onchange="selectIntakeOption('mark_complete')">
            <div>
                <div class="font-medium text-sm">Mark as completed</div>
                <div class="text-xs text-base-content/60">Client completed intake verbally or on paper</div>
            </div>
        </label>

        {{-- Skip/Waive Option (admin only for required) --}}
        <label id="intake-waive-option" class="flex items-start gap-3 p-3 border border-base-300 rounded-lg cursor-pointer hover:bg-base-200/50 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary/5">
            <input type="radio" name="intake_option" value="waive" class="radio radio-sm radio-primary mt-0.5" onchange="selectIntakeOption('waive')">
            <div>
                <div class="font-medium text-sm">Skip for now</div>
                <div class="text-xs text-base-content/60">Proceed without intake questionnaire</div>
            </div>
        </label>

        {{-- Waive Reason (shown when waive is selected and intake is required) --}}
        <div id="intake-waive-reason-section" class="hidden pl-8">
            <div class="form-control">
                <label class="label py-1" for="intake-waive-reason">
                    <span class="label-text text-sm">Reason for waiving <span class="text-error">*</span></span>
                </label>
                <input type="text"
                       id="intake-waive-reason"
                       class="input input-bordered input-sm"
                       placeholder="e.g., Returning client, will complete later"
                       oninput="updateIntakeWaiverReason(this.value)">
                <label class="label py-1">
                    <span class="label-text-alt text-warning">
                        <span class="icon-[tabler--alert-triangle] size-3 mr-1"></span>
                        This action will be logged for compliance
                    </span>
                </label>
            </div>
        </div>
    </div>

    {{-- No Intake Required Message --}}
    <div id="intake-not-required" class="hidden">
        <div class="flex items-center gap-2 text-sm text-success">
            <span class="icon-[tabler--check] size-4"></span>
            <span>No intake questionnaire required for this booking</span>
        </div>
    </div>
</div>
