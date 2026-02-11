@php
    $fieldKey = $field->field_key;
    $fieldValue = $values[$fieldKey] ?? $field->default_value ?? '';
    $isRequired = $field->is_required;
@endphp

<div class="{{ $field->field_type === 'textarea' ? 'md:col-span-2' : '' }}">
    <label class="label-text" for="custom_{{ $fieldKey }}">
        {{ $field->field_label }}
        @if($isRequired) <span class="text-error">*</span> @endif
    </label>

    @switch($field->field_type)
        @case('text')
            <input type="text"
                   id="custom_{{ $fieldKey }}"
                   name="custom_fields[{{ $fieldKey }}]"
                   value="{{ old("custom_fields.$fieldKey", $fieldValue) }}"
                   class="input input-bordered w-full"
                   {{ $isRequired ? 'required' : '' }}>
            @break

        @case('textarea')
            <textarea id="custom_{{ $fieldKey }}"
                      name="custom_fields[{{ $fieldKey }}]"
                      rows="3"
                      class="textarea textarea-bordered w-full"
                      {{ $isRequired ? 'required' : '' }}>{{ old("custom_fields.$fieldKey", $fieldValue) }}</textarea>
            @break

        @case('number')
            <input type="number"
                   id="custom_{{ $fieldKey }}"
                   name="custom_fields[{{ $fieldKey }}]"
                   value="{{ old("custom_fields.$fieldKey", $fieldValue) }}"
                   class="input input-bordered w-full"
                   {{ $isRequired ? 'required' : '' }}>
            @break

        @case('date')
            <input type="date"
                   id="custom_{{ $fieldKey }}"
                   name="custom_fields[{{ $fieldKey }}]"
                   value="{{ old("custom_fields.$fieldKey", $fieldValue) }}"
                   class="input input-bordered w-full"
                   {{ $isRequired ? 'required' : '' }}>
            @break

        @case('dropdown')
            <select id="custom_{{ $fieldKey }}"
                    name="custom_fields[{{ $fieldKey }}]"
                    class="select select-bordered w-full"
                    {{ $isRequired ? 'required' : '' }}>
                <option value="">Select...</option>
                @if($field->options)
                    @foreach($field->options as $option)
                        <option value="{{ $option }}" {{ old("custom_fields.$fieldKey", $fieldValue) === $option ? 'selected' : '' }}>
                            {{ $option }}
                        </option>
                    @endforeach
                @endif
            </select>
            @break

        @case('checkbox')
            @php
                $selectedValues = old("custom_fields.$fieldKey", is_string($fieldValue) ? json_decode($fieldValue, true) : ($fieldValue ?? [])) ?? [];
            @endphp
            <div class="flex flex-wrap gap-3 mt-2">
                @if($field->options)
                    @foreach($field->options as $option)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox"
                                   name="custom_fields[{{ $fieldKey }}][]"
                                   value="{{ $option }}"
                                   class="checkbox checkbox-sm"
                                   {{ in_array($option, $selectedValues) ? 'checked' : '' }}>
                            <span class="text-sm">{{ $option }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
            @break

        @case('yes_no')
            <div class="flex items-center gap-4 mt-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio"
                           name="custom_fields[{{ $fieldKey }}]"
                           value="1"
                           class="radio radio-sm"
                           {{ old("custom_fields.$fieldKey", $fieldValue) == '1' ? 'checked' : '' }}>
                    <span>Yes</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio"
                           name="custom_fields[{{ $fieldKey }}]"
                           value="0"
                           class="radio radio-sm"
                           {{ old("custom_fields.$fieldKey", $fieldValue) == '0' ? 'checked' : '' }}>
                    <span>No</span>
                </label>
            </div>
            @break

        @default
            <input type="text"
                   id="custom_{{ $fieldKey }}"
                   name="custom_fields[{{ $fieldKey }}]"
                   value="{{ old("custom_fields.$fieldKey", $fieldValue) }}"
                   class="input input-bordered w-full"
                   {{ $isRequired ? 'required' : '' }}>
    @endswitch

    @if($field->help_text)
        <p class="text-sm text-base-content/50 mt-1">{{ $field->help_text }}</p>
    @endif
</div>
