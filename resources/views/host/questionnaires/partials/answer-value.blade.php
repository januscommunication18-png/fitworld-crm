@switch($question->question_type)
    @case('yes_no')
        @if($answer->answer === 'yes' || $answer->answer === '1' || $answer->answer === 'true')
            <span class="badge badge-success">Yes</span>
        @else
            <span class="badge badge-error">No</span>
        @endif
        @break

    @case('acknowledgement')
        @if($answer->answer === '1' || $answer->answer === 'true')
            <span class="badge badge-success">
                <span class="icon-[tabler--check] size-3 me-1"></span>
                Acknowledged
            </span>
        @else
            <span class="badge badge-warning">Not Acknowledged</span>
        @endif
        @break

    @case('single_select')
    @case('dropdown')
        @php
            $selectedOption = collect($question->options ?? [])->firstWhere('key', $answer->answer);
        @endphp
        <span class="badge badge-ghost">{{ $selectedOption['label'] ?? $answer->answer }}</span>
        @break

    @case('multi_select')
        @php
            $selectedKeys = json_decode($answer->answer, true) ?? [];
            $options = $question->options ?? [];
        @endphp
        <div class="flex flex-wrap gap-1">
            @forelse($selectedKeys as $key)
                @php
                    $option = collect($options)->firstWhere('key', $key);
                @endphp
                <span class="badge badge-ghost">{{ $option['label'] ?? $key }}</span>
            @empty
                <span class="text-base-content/40 italic text-sm">None selected</span>
            @endforelse
        </div>
        @break

    @case('date')
        @php
            $date = \Carbon\Carbon::parse($answer->answer);
        @endphp
        <span class="text-base-content">{{ $date->format('F j, Y') }}</span>
        @break

    @case('long_text')
        <div class="bg-base-200/50 rounded-lg p-3 text-sm whitespace-pre-wrap">{{ $answer->answer }}</div>
        @break

    @case('email')
        <a href="mailto:{{ $answer->answer }}" class="link link-primary">{{ $answer->answer }}</a>
        @break

    @case('phone')
        <a href="tel:{{ $answer->answer }}" class="link link-primary">{{ $answer->answer }}</a>
        @break

    @default
        <span class="text-base-content">{{ $answer->answer }}</span>
@endswitch
