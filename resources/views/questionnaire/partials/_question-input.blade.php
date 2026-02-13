@php
    $inputId = 'q_' . $question->id;
    $inputName = 'q_' . $question->id;
    $existingValue = $existingAnswers[$question->id]->answer ?? old($inputName) ?? $question->default_value;
    $hasError = $errors->has($inputName);
@endphp

<div class="form-control question-card border border-base-300 rounded-lg p-4 bg-base-100 {{ $hasError ? 'border-error' : '' }}">
    <label class="label pb-1" for="{{ $inputId }}">
        <span class="label-text font-medium text-base">
            {{ $question->question_label }}
            @if($question->is_required)
                <span class="text-error">*</span>
            @endif
        </span>
    </label>

    @if($question->help_text)
        <p class="text-sm text-base-content/60 mb-3">{{ $question->help_text }}</p>
    @endif

    @switch($question->question_type)
        @case('short_text')
            <input type="text"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   placeholder="{{ $question->placeholder ?? '' }}"
                   {{ $question->is_required ? 'required' : '' }}>
            @break

        @case('long_text')
            <textarea id="{{ $inputId }}"
                      name="{{ $inputName }}"
                      class="textarea textarea-bordered tap-target {{ $hasError ? 'textarea-error' : '' }}"
                      rows="4"
                      placeholder="{{ $question->placeholder ?? '' }}"
                      {{ $question->is_required ? 'required' : '' }}>{{ $existingValue }}</textarea>
            @break

        @case('email')
            <input type="email"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   placeholder="{{ $question->placeholder ?? 'email@example.com' }}"
                   {{ $question->is_required ? 'required' : '' }}>
            @break

        @case('phone')
            <input type="tel"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   placeholder="{{ $question->placeholder ?? '(555) 123-4567' }}"
                   {{ $question->is_required ? 'required' : '' }}>
            @break

        @case('number')
            <input type="number"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   placeholder="{{ $question->placeholder ?? '' }}"
                   {{ $question->is_required ? 'required' : '' }}>
            @break

        @case('date')
            <input type="date"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   {{ $question->is_required ? 'required' : '' }}>
            @break

        @case('yes_no')
            <div class="flex gap-4 mt-2">
                <label class="flex items-center gap-3 cursor-pointer tap-target px-4 py-2 border border-base-300 rounded-lg hover:bg-base-200 transition-colors {{ $existingValue === 'yes' ? 'border-primary bg-primary/10' : '' }}">
                    <input type="radio"
                           name="{{ $inputName }}"
                           value="yes"
                           class="radio radio-primary"
                           {{ $existingValue === 'yes' ? 'checked' : '' }}
                           {{ $question->is_required ? 'required' : '' }}>
                    <span class="label-text">Yes</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer tap-target px-4 py-2 border border-base-300 rounded-lg hover:bg-base-200 transition-colors {{ $existingValue === 'no' ? 'border-primary bg-primary/10' : '' }}">
                    <input type="radio"
                           name="{{ $inputName }}"
                           value="no"
                           class="radio radio-primary"
                           {{ $existingValue === 'no' ? 'checked' : '' }}>
                    <span class="label-text">No</span>
                </label>
            </div>
            @break

        @case('single_select')
            <div class="space-y-2 mt-2">
                @if($question->options)
                    @foreach($question->options as $option)
                        @php $optionKey = $option['key'] ?? $option; @endphp
                        <label class="flex items-center gap-3 cursor-pointer tap-target px-4 py-3 border border-base-300 rounded-lg hover:bg-base-200 transition-colors {{ $existingValue === $optionKey ? 'border-primary bg-primary/10' : '' }}">
                            <input type="radio"
                                   name="{{ $inputName }}"
                                   value="{{ $optionKey }}"
                                   class="radio radio-primary"
                                   {{ $existingValue === $optionKey ? 'checked' : '' }}
                                   {{ $question->is_required ? 'required' : '' }}>
                            <span class="label-text">{{ $option['label'] ?? $option }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
            @break

        @case('multi_select')
            @php
                $selectedValues = is_string($existingValue) ? json_decode($existingValue, true) : ($existingValue ?? []);
                $selectedValues = is_array($selectedValues) ? $selectedValues : [];
            @endphp
            <div class="space-y-2 mt-2">
                @if($question->options)
                    @foreach($question->options as $option)
                        @php $optionKey = $option['key'] ?? $option; @endphp
                        <label class="flex items-center gap-3 cursor-pointer tap-target px-4 py-3 border border-base-300 rounded-lg hover:bg-base-200 transition-colors {{ in_array($optionKey, $selectedValues) ? 'border-primary bg-primary/10' : '' }}">
                            <input type="checkbox"
                                   name="{{ $inputName }}[]"
                                   value="{{ $optionKey }}"
                                   class="checkbox checkbox-primary"
                                   {{ in_array($optionKey, $selectedValues) ? 'checked' : '' }}>
                            <span class="label-text">{{ $option['label'] ?? $option }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
            @break

        @case('dropdown')
            <select id="{{ $inputId }}"
                    name="{{ $inputName }}"
                    class="select select-bordered tap-target {{ $hasError ? 'select-error' : '' }}"
                    {{ $question->is_required ? 'required' : '' }}>
                <option value="">Select an option...</option>
                @if($question->options)
                    @foreach($question->options as $option)
                        @php $optionKey = $option['key'] ?? $option; @endphp
                        <option value="{{ $optionKey }}" {{ $existingValue === $optionKey ? 'selected' : '' }}>
                            {{ $option['label'] ?? $option }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break

        @case('acknowledgement')
            <label class="flex items-start gap-3 cursor-pointer tap-target px-4 py-3 border border-base-300 rounded-lg hover:bg-base-200 transition-colors mt-2 {{ $existingValue ? 'border-primary bg-primary/10' : '' }}">
                <input type="checkbox"
                       id="{{ $inputId }}"
                       name="{{ $inputName }}"
                       value="1"
                       class="checkbox checkbox-primary mt-0.5"
                       {{ $existingValue ? 'checked' : '' }}
                       {{ $question->is_required ? 'required' : '' }}>
                <span class="label-text">{{ $question->placeholder ?? 'I acknowledge and agree' }}</span>
            </label>
            @break

        @default
            <input type="text"
                   id="{{ $inputId }}"
                   name="{{ $inputName }}"
                   value="{{ $existingValue }}"
                   class="input input-bordered tap-target {{ $hasError ? 'input-error' : '' }}"
                   placeholder="{{ $question->placeholder ?? '' }}"
                   {{ $question->is_required ? 'required' : '' }}>
    @endswitch

    @if($hasError)
        <label class="label">
            <span class="label-text-alt text-error">{{ $errors->first($inputName) }}</span>
        </label>
    @endif
</div>
