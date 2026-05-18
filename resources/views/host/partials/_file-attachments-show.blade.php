{{-- File Attachments Display Card --}}
@if($fileAttachments && count($fileAttachments) > 0)
@php
    $fileIcons = [
        'pdf' => 'icon-[tabler--file-type-pdf]',
        'doc' => 'icon-[tabler--file-type-doc]',
        'docx' => 'icon-[tabler--file-type-doc]',
        'xls' => 'icon-[tabler--file-type-xls]',
        'xlsx' => 'icon-[tabler--file-type-xls]',
        'jpg' => 'icon-[tabler--photo]',
        'jpeg' => 'icon-[tabler--photo]',
        'png' => 'icon-[tabler--photo]',
        'webp' => 'icon-[tabler--photo]',
    ];
    $uploadsDisk = config('filesystems.uploads');
@endphp
<div class="card bg-base-100">
    <div class="card-body">
        <h2 class="card-title text-lg">
            <span class="icon-[tabler--paperclip] size-5"></span>
            Attachments
        </h2>
        <div class="space-y-2 mt-3">
            @foreach($fileAttachments as $file)
                @php
                    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $icon = $fileIcons[$ext] ?? 'icon-[tabler--file]';
                    $size = isset($file['size']) ? round($file['size'] / 1024 / 1024, 1) : null;
                    $url = Storage::disk($uploadsDisk)->url($file['path']);
                @endphp
                <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                    <span class="{{ $icon }} size-6 text-base-content/60 shrink-0"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $file['name'] }}</p>
                        @if($size)
                            <p class="text-xs text-base-content/50">{{ $size }} MB</p>
                        @endif
                    </div>
                    <a href="{{ $url }}" target="_blank" download="{{ $file['name'] }}" class="btn btn-ghost btn-sm btn-circle">
                        <span class="icon-[tabler--download] size-5"></span>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
