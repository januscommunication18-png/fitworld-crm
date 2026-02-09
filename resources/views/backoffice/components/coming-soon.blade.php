@props(['title' => 'Coming Soon', 'description' => 'This feature is under development.', 'icon' => 'tabler--clock'])

<div class="flex flex-col items-center justify-center py-16">
    <div class="w-24 h-24 bg-base-200 rounded-full flex items-center justify-center mb-6">
        <span class="icon-[{{ $icon }}] size-12 text-base-content/30"></span>
    </div>
    <h2 class="text-2xl font-bold text-base-content mb-2">{{ $title }}</h2>
    <p class="text-base-content/60 text-center max-w-md">{{ $description }}</p>
</div>
