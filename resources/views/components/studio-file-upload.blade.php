{{--
    Studio File Upload Component

    A reusable file upload card with drag & drop support and file list preview.

    Usage:
    <x-studio-file-upload
        name="attachments"
        :files="$model?->attachments ?? []"
        title="Attachments"
        help="Upload PDF, Word, or image files."
    />

    Props:
    - name:     Input field name (required). Files submitted as name[]
    - files:    Array of existing file URLs/paths for preview (default: [])
    - title:    Card title (default: 'Attachments')
    - help:     Help text (default: null)
    - accept:   Accepted file types (default: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp')
    - max-size: Max file size in MB (default: 10)
    - multiple: Allow multiple files (default: true)
--}}

@props([
    'name',
    'files' => [],
    'title' => 'Attachments',
    'help' => null,
    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp',
    'maxSize' => 10,
    'multiple' => true,
])

@php
    $inputId = 'file-upload-' . str_replace(['[', ']', '.'], '-', $name);
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
@endphp

<div class="card bg-base-100">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
        @if($help)
            <p class="text-sm text-base-content/60 mb-3">{{ $help }}</p>
        @endif

        <input type="file" id="{{ $inputId }}" name="{{ $name }}[]" class="hidden"
               accept="{{ $accept }}" {{ $multiple ? 'multiple' : '' }}>

        {{-- Existing files --}}
        @if(is_array($files) && count($files) > 0)
            @php
                $uploadsDisk = config('filesystems.uploads');
            @endphp
            <div id="{{ $inputId }}-existing" class="space-y-2 mb-3">
                @foreach($files as $file)
                    @php
                        $fileName = is_array($file) ? ($file['name'] ?? basename($file['path'] ?? '')) : (is_string($file) ? basename($file) : '');
                        $filePath = is_array($file) ? ($file['path'] ?? '') : (is_string($file) ? $file : '');
                        $fileSize = is_array($file) && isset($file['size']) ? round($file['size'] / 1024 / 1024, 1) : null;
                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $icon = $fileIcons[$ext] ?? 'icon-[tabler--file]';
                        $url = $filePath ? Storage::disk($uploadsDisk)->url($filePath) : '#';
                    @endphp
                    <div class="flex items-center gap-3 p-2 bg-base-200/50 rounded-lg">
                        <span class="{{ $icon }} size-5 text-base-content/60 shrink-0"></span>
                        <span class="text-sm flex-1 truncate">{{ $fileName }}</span>
                        @if($fileSize)
                            <span class="text-xs text-base-content/50">{{ $fileSize }} MB</span>
                        @endif
                        <a href="{{ $url }}" target="_blank" download="{{ $fileName }}" class="btn btn-ghost btn-xs btn-circle">
                            <span class="icon-[tabler--download] size-4"></span>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- New files preview --}}
        <div id="{{ $inputId }}-preview" class="space-y-2 mb-3 hidden"></div>

        {{-- Upload zone --}}
        <div id="{{ $inputId }}-zone"
             class="border-2 border-dashed border-base-content/20 rounded-xl p-6 text-center cursor-pointer hover:border-primary/50 hover:bg-primary/5 transition-colors"
             onclick="document.getElementById('{{ $inputId }}').click()"
             ondragover="event.preventDefault(); this.classList.add('border-primary', 'bg-primary/5')"
             ondragleave="this.classList.remove('border-primary', 'bg-primary/5')"
             ondrop="event.preventDefault(); this.classList.remove('border-primary', 'bg-primary/5'); studioFileUploadDrop(event, '{{ $inputId }}')">
            <span class="icon-[tabler--upload] size-8 text-base-content/30 mx-auto block mb-2"></span>
            <p class="text-sm font-medium text-base-content/70">Click to upload or drag & drop</p>
            <p class="text-xs text-base-content/50 mt-1">Max {{ $maxSize }}MB per file. {{ strtoupper(str_replace('.', '', str_replace(',', ', ', $accept))) }}</p>
        </div>

        @error($name)
            <p class="text-error text-sm mt-1">{{ $message }}</p>
        @enderror
        @error($name . '.*')
            <p class="text-error text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>

@once
@push('scripts')
<script>
    // Store accumulated files per input
    var _studioFileBuffers = {};

    var _studioFileIconMap = {
        'pdf': 'icon-[tabler--file-type-pdf]',
        'doc': 'icon-[tabler--file-type-doc]',
        'docx': 'icon-[tabler--file-type-doc]',
        'xls': 'icon-[tabler--file-type-xls]',
        'xlsx': 'icon-[tabler--file-type-xls]',
        'jpg': 'icon-[tabler--photo]',
        'jpeg': 'icon-[tabler--photo]',
        'png': 'icon-[tabler--photo]',
        'webp': 'icon-[tabler--photo]',
    };

    function _studioFileAddFiles(inputId, newFiles) {
        if (!_studioFileBuffers[inputId]) _studioFileBuffers[inputId] = [];
        for (var i = 0; i < newFiles.length; i++) {
            _studioFileBuffers[inputId].push(newFiles[i]);
        }
        _studioFileSyncInput(inputId);
        _studioFileRenderPreview(inputId);
    }

    function _studioFileRemove(inputId, index) {
        if (_studioFileBuffers[inputId]) {
            _studioFileBuffers[inputId].splice(index, 1);
        }
        _studioFileSyncInput(inputId);
        _studioFileRenderPreview(inputId);
    }

    function _studioFileSyncInput(inputId) {
        var input = document.getElementById(inputId);
        var dt = new DataTransfer();
        var files = _studioFileBuffers[inputId] || [];
        for (var i = 0; i < files.length; i++) {
            dt.items.add(files[i]);
        }
        input.files = dt.files;
    }

    function _studioFileRenderPreview(inputId) {
        var preview = document.getElementById(inputId + '-preview');
        if (!preview) return;

        var files = _studioFileBuffers[inputId] || [];
        preview.innerHTML = '';

        if (files.length === 0) {
            preview.classList.add('hidden');
            return;
        }
        preview.classList.remove('hidden');

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var ext = file.name.split('.').pop().toLowerCase();
            var icon = _studioFileIconMap[ext] || 'icon-[tabler--file]';
            var size = (file.size / 1024 / 1024).toFixed(1);

            var row = document.createElement('div');
            row.className = 'flex items-center gap-3 p-2 bg-primary/5 rounded-lg';
            row.innerHTML = '<span class="' + icon + ' size-5 text-primary shrink-0"></span>' +
                '<span class="text-sm flex-1 truncate">' + file.name + '</span>' +
                '<span class="text-xs text-base-content/50">' + size + ' MB</span>' +
                '<button type="button" class="btn btn-ghost btn-xs btn-circle" onclick="_studioFileRemove(\'' + inputId + '\', ' + i + ')">' +
                '<span class="icon-[tabler--x] size-4"></span></button>';
            preview.appendChild(row);
        }
    }

    function studioFileUploadDrop(e, inputId) {
        _studioFileAddFiles(inputId, e.dataTransfer.files);
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[id^="file-upload-"]').forEach(function(input) {
            input.addEventListener('change', function() {
                // Accumulate files from new selection
                _studioFileAddFiles(this.id, this.files);
            });
        });
    });
</script>
@endpush
@endonce
