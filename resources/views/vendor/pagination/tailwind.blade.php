@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        {{-- Results Info --}}
        <div class="text-sm text-base-content/60">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-medium text-base-content">{{ $paginator->firstItem() }}</span>
                {!! __('to') !!}
                <span class="font-medium text-base-content">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            {!! __('of') !!}
            <span class="font-medium text-base-content">{{ $paginator->total() }}</span>
            {!! __('results') !!}
        </div>

        {{-- Pagination Links --}}
        <div class="join">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="join-item btn btn-sm btn-disabled">
                    <span class="icon-[tabler--chevron-left] size-4"></span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="join-item btn btn-sm" aria-label="{{ __('pagination.previous') }}">
                    <span class="icon-[tabler--chevron-left] size-4"></span>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="join-item btn btn-sm btn-disabled">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="join-item btn btn-sm btn-primary">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="join-item btn btn-sm" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="join-item btn btn-sm" aria-label="{{ __('pagination.next') }}">
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </a>
            @else
                <span class="join-item btn btn-sm btn-disabled">
                    <span class="icon-[tabler--chevron-right] size-4"></span>
                </span>
            @endif
        </div>
    </nav>
@endif
