@php
    $hasTitle = !empty($block->title);
    $isCardStyle = $block->isCardStyle();
    $blockNumber = isset($blockIndex) ? $blockIndex + 1 : null;
@endphp

<div class="{{ $isCardStyle ? 'bg-base-200/30 rounded-xl p-5 border border-base-200' : '' }}">
    {{-- Block Header --}}
    @if($hasTitle)
        <div class="flex items-start gap-3 mb-5">
            @if($blockNumber)
                <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="text-sm font-bold text-primary">{{ $blockNumber }}</span>
                </div>
            @endif
            <div class="flex-1">
                <h4 class="font-semibold text-base-content">{{ $block->title }}</h4>
                @if($block->description)
                    <p class="text-sm text-base-content/60 mt-0.5">{{ $block->description }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Questions --}}
    <div class="space-y-6">
        @foreach($block->questions as $qIndex => $question)
            <div class="group">
                {{-- Question Label --}}
                <label class="block mb-2" for="q_{{ $question->id }}">
                    <span class="text-sm font-medium text-base-content">
                        {{ $question->question_label }}
                    </span>
                    @if($question->is_required)
                        <span class="text-error ml-0.5">*</span>
                    @endif
                </label>

                {{-- Question Input --}}
                @switch($question->question_type)
                    @case('short_text')
                        <input type="text"
                               id="q_{{ $question->id }}"
                               class="input input-bordered w-full focus:input-primary transition-colors"
                               placeholder="{{ $question->placeholder ?? 'Enter your answer...' }}"
                               {{ $question->is_required ? 'required' : '' }}>
                        @break

                    @case('long_text')
                        <textarea id="q_{{ $question->id }}"
                                  class="textarea textarea-bordered w-full focus:textarea-primary transition-colors resize-none"
                                  rows="4"
                                  placeholder="{{ $question->placeholder ?? 'Enter your detailed answer...' }}"
                                  {{ $question->is_required ? 'required' : '' }}></textarea>
                        @break

                    @case('email')
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 icon-[tabler--mail] size-5 text-base-content/40"></span>
                            <input type="email"
                                   id="q_{{ $question->id }}"
                                   class="input input-bordered w-full pl-10 focus:input-primary transition-colors"
                                   placeholder="{{ $question->placeholder ?? 'email@example.com' }}"
                                   {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @break

                    @case('phone')
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 icon-[tabler--phone] size-5 text-base-content/40"></span>
                            <input type="tel"
                                   id="q_{{ $question->id }}"
                                   class="input input-bordered w-full pl-10 focus:input-primary transition-colors"
                                   placeholder="{{ $question->placeholder ?? '(555) 123-4567' }}"
                                   {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @break

                    @case('number')
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 icon-[tabler--hash] size-5 text-base-content/40"></span>
                            <input type="number"
                                   id="q_{{ $question->id }}"
                                   class="input input-bordered w-full pl-10 focus:input-primary transition-colors"
                                   placeholder="{{ $question->placeholder ?? '0' }}"
                                   {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @break

                    @case('date')
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 icon-[tabler--calendar] size-5 text-base-content/40"></span>
                            <input type="date"
                                   id="q_{{ $question->id }}"
                                   class="input input-bordered w-full pl-10 focus:input-primary transition-colors"
                                   {{ $question->is_required ? 'required' : '' }}>
                        </div>
                        @break

                    @case('yes_no')
                        <div class="flex flex-wrap gap-3">
                            <label class="flex-1 min-w-[120px]">
                                <input type="radio" name="q_{{ $question->id }}" value="yes" class="peer hidden" {{ $question->is_required ? 'required' : '' }}>
                                <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer transition-all peer-checked:border-success peer-checked:bg-success/10 hover:border-base-300">
                                    <span class="icon-[tabler--check] size-5 text-success"></span>
                                    <span class="font-medium">Yes</span>
                                </div>
                            </label>
                            <label class="flex-1 min-w-[120px]">
                                <input type="radio" name="q_{{ $question->id }}" value="no" class="peer hidden">
                                <div class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer transition-all peer-checked:border-error peer-checked:bg-error/10 hover:border-base-300">
                                    <span class="icon-[tabler--x] size-5 text-error"></span>
                                    <span class="font-medium">No</span>
                                </div>
                            </label>
                        </div>
                        @break

                    @case('single_select')
                        @if($question->options && count($question->options) > 0)
                            <div class="space-y-2">
                                @foreach($question->options as $optIndex => $option)
                                    @php
                                        $optionKey = $option['key'] ?? $option;
                                        $optionLabel = $option['label'] ?? $option;
                                    @endphp
                                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer transition-all hover:border-base-300 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="radio"
                                               name="q_{{ $question->id }}"
                                               value="{{ $optionKey }}"
                                               class="radio radio-primary radio-sm"
                                               {{ $question->is_required && $optIndex === 0 ? 'required' : '' }}>
                                        <span class="text-base-content">{{ $optionLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-base-content/50 italic">No options defined</p>
                        @endif
                        @break

                    @case('multi_select')
                        @if($question->options && count($question->options) > 0)
                            <div class="space-y-2">
                                @foreach($question->options as $option)
                                    @php
                                        $optionKey = $option['key'] ?? $option;
                                        $optionLabel = $option['label'] ?? $option;
                                    @endphp
                                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer transition-all hover:border-base-300 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                        <input type="checkbox"
                                               name="q_{{ $question->id }}[]"
                                               value="{{ $optionKey }}"
                                               class="checkbox checkbox-primary checkbox-sm">
                                        <span class="text-base-content">{{ $optionLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-base-content/50 italic">No options defined</p>
                        @endif
                        @break

                    @case('dropdown')
                        <div class="relative">
                            <select id="q_{{ $question->id }}"
                                    class="select select-bordered w-full focus:select-primary transition-colors"
                                    {{ $question->is_required ? 'required' : '' }}>
                                <option value="" disabled selected>Select an option...</option>
                                @if($question->options)
                                    @foreach($question->options as $option)
                                        @php
                                            $optionKey = $option['key'] ?? $option;
                                            $optionLabel = $option['label'] ?? $option;
                                        @endphp
                                        <option value="{{ $optionKey }}">{{ $optionLabel }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @break

                    @case('scale')
                        @php
                            $minVal = $question->min_value ?? 1;
                            $maxVal = $question->max_value ?? 5;
                            $minLabel = $question->min_label ?? '';
                            $maxLabel = $question->max_label ?? '';
                        @endphp
                        <div>
                            @if($minLabel || $maxLabel)
                                <div class="flex justify-between text-xs text-base-content/50 mb-2">
                                    <span>{{ $minLabel }}</span>
                                    <span>{{ $maxLabel }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-1">
                                @for($i = $minVal; $i <= $maxVal; $i++)
                                    <label class="flex-1">
                                        <input type="radio" name="q_{{ $question->id }}" value="{{ $i }}" class="peer hidden" {{ $question->is_required && $i === $minVal ? 'required' : '' }}>
                                        <div class="flex items-center justify-center h-12 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer transition-all peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-content hover:border-base-300 font-semibold">
                                            {{ $i }}
                                        </div>
                                    </label>
                                @endfor
                            </div>
                        </div>
                        @break

                    @case('rating')
                        @php
                            $maxStars = $question->max_value ?? 5;
                        @endphp
                        <div class="flex items-center gap-1" id="rating_{{ $question->id }}">
                            @for($i = 1; $i <= $maxStars; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="q_{{ $question->id }}" value="{{ $i }}" class="peer hidden" {{ $question->is_required && $i === 1 ? 'required' : '' }}>
                                    <span class="icon-[tabler--star-filled] size-8 text-base-300 peer-checked:text-warning hover:text-warning/70 transition-colors"></span>
                                </label>
                            @endfor
                        </div>
                        @break

                    @case('acknowledgement')
                        <label class="flex items-start gap-3 p-4 rounded-xl border-2 border-base-200 bg-base-100 cursor-pointer hover:border-base-300 transition-all has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                            <input type="checkbox"
                                   id="q_{{ $question->id }}"
                                   class="checkbox checkbox-primary mt-0.5"
                                   {{ $question->is_required ? 'required' : '' }}>
                            <span class="text-base-content">{{ $question->placeholder ?? 'I acknowledge and agree to the above statement' }}</span>
                        </label>
                        @break

                    @case('file')
                        <div class="border-2 border-dashed border-base-300 rounded-xl p-6 text-center hover:border-primary/50 transition-colors cursor-pointer bg-base-100">
                            <input type="file" id="q_{{ $question->id }}" class="hidden" {{ $question->is_required ? 'required' : '' }}>
                            <label for="q_{{ $question->id }}" class="cursor-pointer">
                                <span class="icon-[tabler--cloud-upload] size-10 text-base-content/30 mx-auto mb-2 block"></span>
                                <span class="text-sm font-medium text-base-content">Click to upload</span>
                                <span class="text-xs text-base-content/50 block mt-1">or drag and drop</span>
                            </label>
                        </div>
                        @break

                    @case('signature')
                        <div class="border-2 border-base-300 rounded-xl p-4 bg-base-100">
                            <div class="bg-base-200/50 rounded-lg h-32 flex items-center justify-center mb-3">
                                <span class="text-base-content/40 text-sm">Sign here</span>
                            </div>
                            <div class="flex justify-end">
                                <button type="button" class="btn btn-ghost btn-xs">
                                    <span class="icon-[tabler--eraser] size-4"></span>
                                    Clear
                                </button>
                            </div>
                        </div>
                        @break

                    @default
                        <input type="text"
                               id="q_{{ $question->id }}"
                               class="input input-bordered w-full focus:input-primary transition-colors"
                               placeholder="{{ $question->placeholder ?? 'Enter your answer...' }}"
                               {{ $question->is_required ? 'required' : '' }}>
                @endswitch

                {{-- Help Text --}}
                @if($question->help_text)
                    <p class="mt-2 text-xs text-base-content/50 flex items-start gap-1.5">
                        <span class="icon-[tabler--info-circle] size-3.5 flex-shrink-0 mt-0.5"></span>
                        {{ $question->help_text }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
</div>
