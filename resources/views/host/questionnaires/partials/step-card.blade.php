<div class="card bg-base-100" data-step-id="{{ $step->id }}">
    <div class="card-body">
        {{-- Step Header --}}
        <div class="flex items-start justify-between gap-4 mb-4 pb-4 border-b border-base-content/10">
            <div class="flex items-center gap-3">
                <span class="icon-[tabler--grip-vertical] size-5 text-base-content/30 cursor-move"></span>
                <div class="w-8 h-8 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold">
                    {{ $stepIndex + 1 }}
                </div>
                <div>
                    <h3 class="font-semibold">{{ $step->title }}</h3>
                    @if($step->description)
                        <p class="text-sm text-base-content/60">{{ $step->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-1">
                <span class="badge badge-ghost badge-sm">{{ $step->blocks->count() }} section(s)</span>
                <div class="relative">
                    <details class="dropdown dropdown-bottom dropdown-end">
                        <summary class="btn btn-ghost btn-xs btn-square list-none cursor-pointer">
                            <span class="icon-[tabler--dots-vertical] size-4"></span>
                        </summary>
                        <ul class="dropdown-content menu bg-base-100 rounded-box w-44 p-2 shadow-lg border border-base-300" style="z-index: 9999; position: absolute; right: 0; top: 100%;">
                            <li><a href="javascript:void(0)" class="edit-step-btn" data-step-id="{{ $step->id }}">
                                <span class="icon-[tabler--edit] size-4"></span> Edit Step
                            </a></li>
                            <li><a href="javascript:void(0)" class="add-block-to-step-btn" data-step-id="{{ $step->id }}">
                                <span class="icon-[tabler--layout-grid-add] size-4"></span> Add Section
                            </a></li>
                            <li><a href="javascript:void(0)" class="delete-step-btn text-error" data-step-id="{{ $step->id }}">
                                <span class="icon-[tabler--trash] size-4"></span> Delete Step
                            </a></li>
                        </ul>
                    </details>
                </div>
            </div>
        </div>

        {{-- Blocks within this step --}}
        <div class="space-y-4 step-blocks-container" data-step-id="{{ $step->id }}">
            @forelse($step->blocks as $blockIndex => $block)
                @include('host.questionnaires.partials.block-card', ['block' => $block, 'blockIndex' => $blockIndex])
            @empty
                <div class="text-center py-8 border-2 border-dashed border-primary/20 rounded-lg empty-step-placeholder bg-primary/5">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary/10 mb-3">
                        <span class="icon-[tabler--layout-grid-add] size-5 text-primary"></span>
                    </div>
                    <p class="text-sm font-medium text-base-content/70 mb-1">Step 2: Add a Section</p>
                    <p class="text-xs text-base-content/50 mb-4">Sections help organize your questions into groups</p>
                    <button type="button" class="btn btn-primary btn-sm add-block-to-step-btn" data-step-id="{{ $step->id }}">
                        <span class="icon-[tabler--plus] size-4"></span>
                        Add Section
                    </button>
                </div>
            @endforelse
        </div>
    </div>
</div>
