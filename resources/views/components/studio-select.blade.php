{{--
    Studio Select Component

    Always renders the advance-select styled dropdown.
    Automatically adds search when options > 5.

    Usage:
    <x-studio-select name="category" :options="$categories" :selected="$plan->category" placeholder="Select a category..." required />

    With slot:
    <x-studio-select name="field" :option-count="count($items)" placeholder="Select...">
        <option value="">Select...</option>
        @foreach($items as $val => $label)
            <option value="{{ $val }}">{{ $label }}</option>
        @endforeach
    </x-studio-select>

    Props:
    - name:             Input name (required)
    - options:          Associative array [value => label] (default: [])
    - selected:         Currently selected value (default: null)
    - placeholder:      Placeholder text (default: 'Select...')
    - required:         Whether field is required (default: false)
    - id:               Input ID (default: name)
    - option-count:     Override option count for search threshold when using slot (default: null)
    - search-threshold: Options above this get search (default: 5)
--}}

@props([
    'name',
    'options' => [],
    'selected' => null,
    'placeholder' => 'Select...',
    'required' => false,
    'id' => null,
    'optionCount' => null,
    'searchThreshold' => 5,
])

@php
    $inputId = $id ?? $name;
    $selectedValue = old($name, $selected);
    $count = $optionCount ?? count($options);
    $useSearch = $count > $searchThreshold;
    $hasSlot = isset($slot) && !$slot->isEmpty();

    $config = [
        'placeholder' => $placeholder,
        'toggleTag' => '<button type="button" aria-expanded="false"></button>',
        'toggleClasses' => 'advance-select-toggle',
        'dropdownClasses' => 'advance-select-menu max-h-72 overflow-y-auto',
        'optionClasses' => 'advance-select-option selected:select-active',
        'optionTemplate' => '<div class="flex justify-between items-center w-full"><span data-title></span><span class="icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block"></span></div>',
        'extraMarkup' => '<span class="icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/50 absolute top-1/2 end-3 -translate-y-1/2"></span>',
    ];

    if ($useSearch) {
        $config['hasSearch'] = true;
        $config['searchPlaceholder'] = 'Search...';
    }
@endphp

<select id="{{ $inputId }}" name="{{ $name }}" class="hidden" {{ $required ? 'required' : '' }}
    data-select='{!! json_encode($config, JSON_UNESCAPED_SLASHES) !!}'>
    @if($hasSlot)
        {{ $slot }}
    @else
        <option value="">{{ $placeholder }}</option>
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ (string) $selectedValue === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    @endif
</select>
