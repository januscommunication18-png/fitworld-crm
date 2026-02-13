<div class="card bg-base-100" data-block-id="{{ $block->id }}">
    <div class="card-body">
        {{-- Block Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--grip-vertical] size-5 text-base-content/30 cursor-move"></span>
                <div>
                    <h4 class="font-semibold">{{ $block->title }}</h4>
                    @if($block->description)
                        <p class="text-sm text-base-content/60">{{ $block->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1">
                <span class="badge badge-ghost badge-sm">{{ $block->isCardStyle() ? 'Card' : 'Plain' }}</span>
                @if($block->isInternal())
                    <span class="badge badge-warning badge-sm">Internal</span>
                @endif
                <div class="relative">
                    <details class="dropdown dropdown-bottom dropdown-end">
                        <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                        </summary>
                        <ul class="dropdown-content menu bg-base-100 rounded-box w-40 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                            <li><a href="javascript:void(0)" class="edit-block-btn" data-block-id="{{ $block->id }}">
                                <span class="icon-[tabler--edit] size-4"></span> Edit
                            </a></li>
                            <li><a href="javascript:void(0)" class="delete-block-btn text-error" data-block-id="{{ $block->id }}">
                                <span class="icon-[tabler--trash] size-4"></span> Delete
                            </a></li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        {{-- Questions List (Drop Zone) --}}
        <div class="space-y-3 questions-container question-drop-zone min-h-[60px] rounded-lg transition-all duration-200" data-block-id="{{ $block->id }}">
            @forelse($block->questions as $question)
                @include('host.questionnaires.partials.question-row', ['question' => $question])
            @empty
                <div class="text-center py-8 border-2 border-dashed border-primary/20 rounded-lg empty-questions-placeholder bg-primary/5 transition-all duration-200">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary/10 mb-2">
                        <span class="icon-[tabler--drag-drop] size-5 text-primary"></span>
                    </div>
                    @if($block->step_id)
                        <p class="text-sm font-medium text-base-content/70 mb-1">Add Questions</p>
                    @endif
                    <p class="text-xs text-base-content/50">Drag a question type here or click below</p>
                </div>
            @endforelse
        </div>

        {{-- Add Question Button --}}
        <button type="button" class="btn btn-ghost btn-sm btn-block mt-4 add-question-to-block-btn" data-block-id="{{ $block->id }}">
            <span class="icon-[tabler--plus] size-4"></span>
            Add Question to this Section
        </button>
    </div>
</div>
