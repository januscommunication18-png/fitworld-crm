@props([
    'id' => 'tags-config',
    'title' => 'Configure Tags',
    'tags',
    'storeRoute',
    'updateRoute',   // URL with __ID__ placeholder, e.g. route('clients.tags.update-tag', ['tag' => '__ID__'])
    'toggleRoute' => null, // optional URL with __ID__ placeholder for active toggle
    'destroyRoute',  // URL with __ID__ placeholder
    'colors' => null,
    'addLabel' => 'Add a new tag',
    'existingLabel' => 'Existing tags',
    'placeholder' => 'e.g., Winter Promo',
    'emptyLabel' => 'No tags yet. Add one above.',
    'usesLabel' => 'uses',
    'deleteConfirm' => 'Delete this tag? Clients tagged with it will lose this label.',
    'userTagsLabel' => 'Studio Client Tags',
    'showPresetSection' => true,
    'showActiveToggle' => true,
])

@php
    $colors = $colors ?? \App\Models\Tag::getDefaultColors();
    $userTags = $tags->filter(fn($t) => !($t->is_preset ?? false))->sortBy('name')->values();
    $presetTags = $tags->filter(fn($t) => $t->is_preset ?? false)->sortBy('name')->values();
@endphp

<x-detail-drawer :id="$id" :title="$title" size="3xl" :showFooter="false">
    <div
        class="space-y-6"
        data-tag-drawer-root="{{ $id }}"
        data-tag-store-url="{{ $storeRoute }}"
        data-tag-update-url="{{ $updateRoute }}"
        data-tag-toggle-url="{{ $toggleRoute ?? '' }}"
        data-tag-destroy-url="{{ $destroyRoute }}"
        data-tag-delete-confirm="{{ $deleteConfirm }}"
        data-tag-uses-label="{{ $usesLabel }}"
        data-tag-colors="{{ json_encode($colors, JSON_UNESCAPED_SLASHES) }}"
    >
        {{-- Add new tag --}}
        <div class="border border-base-300 rounded-lg p-4">
            <form data-tag-create-form class="space-y-3">
                @csrf
                <div>
                    <label class="label-text" for="{{ $id }}-tag-create-name">Name <span class="text-error">*</span></label>
                    <input type="text" id="{{ $id }}-tag-create-name" data-tag-create-name name="name" class="input w-full" placeholder="{{ $placeholder }}" required>
                </div>
                <div>
                    <label class="label-text">Color</label>
                    <div class="flex flex-wrap gap-2 mt-1" data-tag-create-color-picker>
                        @foreach($colors as $i => $color)
                            <label class="cursor-pointer">
                                <input type="radio" name="color" value="{{ $color }}" class="peer hidden" {{ $i === 0 ? 'checked' : '' }}>
                                <span class="block size-7 rounded-full border-2 border-transparent peer-checked:border-base-content/40 ring-2 ring-transparent peer-checked:ring-base-content/20 transition-all" style="background-color: {{ $color }};"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div data-tag-create-error class="alert alert-error alert-sm hidden" role="alert">
                    <span class="icon-[tabler--alert-circle] size-4"></span>
                    <span data-tag-create-error-msg></span>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" data-tag-create-btn>
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Tag
                </button>
            </form>
        </div>

        {{-- Your tags (user-added) --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--user-plus] size-4 text-primary"></span>
                    {{ $userTagsLabel }}
                </h4>
                <span class="badge badge-soft badge-primary" data-tag-user-count>{{ $userTags->count() }}</span>
            </div>
            <ul data-tag-list="user" class="space-y-2">
                @forelse($userTags as $tag)
                    @php
                        $isActive = (bool) ($tag->is_active ?? true);
                    @endphp
                    <li class="tag-row flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/60 group {{ $isActive ? '' : 'opacity-60' }}"
                        data-tag-id="{{ $tag->id }}"
                        data-tag-name="{{ $tag->name }}"
                        data-tag-color="{{ $tag->color }}"
                        data-tag-is-preset="0"
                        data-tag-is-active="{{ $isActive ? '1' : '0' }}">
                        <span class="tag-name flex-1 truncate font-medium">{{ $tag->name }}</span>
                        <span class="tag-color-code text-xs font-mono text-base-content/60 shrink-0">{{ strtoupper($tag->color) }}</span>
                        <span class="tag-swatch size-5 rounded-full shrink-0 border border-base-content/10" style="background-color: {{ $tag->color }};"></span>
                        @if($showActiveToggle)
                            <label class="switch switch-primary switch-sm shrink-0" title="{{ $isActive ? 'Active' : 'Inactive' }}">
                                <input type="checkbox" data-tag-toggle {{ $isActive ? 'checked' : '' }}>
                                <span class="switch-indicator"></span>
                            </label>
                            <span class="tag-status-label text-xs shrink-0 w-14 text-right {{ $isActive ? 'text-success' : 'text-base-content/50' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                        @endif
                        <div class="tag-actions flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" class="btn btn-ghost btn-xs btn-square" title="Edit" data-tag-edit>
                                <span class="icon-[tabler--edit] size-4"></span>
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete" data-tag-delete>
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </div>
                    </li>
                @empty
                    <li class="text-center py-4 text-sm text-base-content/50 border border-dashed border-base-300 rounded-lg" data-tag-empty-state="user">
                        {{ $emptyLabel }}
                    </li>
                @endforelse
            </ul>
        </div>

        @if($showPresetSection)
        {{-- Preset tags (defaults) --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-semibold flex items-center gap-2">
                    <span class="icon-[tabler--stack-2] size-4 text-base-content/60"></span>
                    Preset tags
                </h4>
                <span class="badge badge-soft badge-neutral" data-tag-preset-count>{{ $presetTags->count() }}</span>
            </div>
            <ul data-tag-list="preset" class="space-y-2">
                @forelse($presetTags as $tag)
                    @php
                        $isActive = (bool) ($tag->is_active ?? true);
                    @endphp
                    <li class="tag-row flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/60 group {{ $isActive ? '' : 'opacity-60' }}"
                        data-tag-id="{{ $tag->id }}"
                        data-tag-name="{{ $tag->name }}"
                        data-tag-color="{{ $tag->color }}"
                        data-tag-is-preset="1"
                        data-tag-is-active="{{ $isActive ? '1' : '0' }}">
                        <span class="tag-name flex-1 truncate font-medium">{{ $tag->name }}</span>
                        <span class="tag-color-code text-xs font-mono text-base-content/60 shrink-0">{{ strtoupper($tag->color) }}</span>
                        <span class="tag-swatch size-5 rounded-full shrink-0 border border-base-content/10" style="background-color: {{ $tag->color }};"></span>
                        <label class="switch switch-primary switch-sm shrink-0" title="{{ $isActive ? 'Active' : 'Inactive' }}">
                            <input type="checkbox" data-tag-toggle {{ $isActive ? 'checked' : '' }}>
                            <span class="switch-indicator"></span>
                        </label>
                        <span class="tag-status-label text-xs shrink-0 w-14 text-right {{ $isActive ? 'text-success' : 'text-base-content/50' }}">{{ $isActive ? 'Active' : 'Inactive' }}</span>
                        <div class="tag-actions flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button type="button" class="btn btn-ghost btn-xs btn-square" title="Edit" data-tag-edit>
                                <span class="icon-[tabler--edit] size-4"></span>
                            </button>
                            <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete" data-tag-delete>
                                <span class="icon-[tabler--trash] size-4"></span>
                            </button>
                        </div>
                    </li>
                @empty
                    <li class="text-center py-4 text-sm text-base-content/50 border border-dashed border-base-300 rounded-lg">No preset tags yet.</li>
                @endforelse
            </ul>
        </div>
        @endif
    </div>
</x-detail-drawer>

{{-- Delete confirmation modal --}}
<div id="{{ $id }}-delete-modal" class="overlay modal overlay-open:opacity-100 overlay-open:duration-300 modal-middle hidden" role="dialog" tabindex="-1" data-tag-delete-modal-for="{{ $id }}">
    <div class="modal-dialog max-w-md">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title flex items-center gap-2">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                    Delete tag?
                </h3>
                <button type="button" class="btn btn-text btn-circle btn-sm absolute end-3 top-3" aria-label="Close" data-tag-delete-cancel>
                    <span class="icon-[tabler--x] size-4"></span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-sm">
                    You're about to delete the tag <strong data-tag-delete-name class="break-words">…</strong>.
                    Clients tagged with it will lose this label. This can't be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-soft btn-secondary" data-tag-delete-cancel>Cancel</button>
                <button type="button" class="btn btn-error" data-tag-delete-confirm>
                    <span class="icon-[tabler--trash] size-4"></span>
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
    <script>
    /**
     * Tag-config drawer wiring.
     * One handler binds all drawer instances on the page by reading config from
     * data attributes, so a page can safely mount more than one instance.
     */
    (function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;

        function bumpCountBy(root, which, delta) {
            const sel = which === 'preset' ? '[data-tag-preset-count]' : '[data-tag-user-count]';
            const badge = root.querySelector(sel);
            if (!badge) return;
            const next = (parseInt(badge.textContent || '0', 10) || 0) + delta;
            badge.textContent = next;
        }

        function showCreateError(root, msg) {
            const box = root.querySelector('[data-tag-create-error]');
            const msgEl = root.querySelector('[data-tag-create-error-msg]');
            if (msgEl) msgEl.textContent = msg;
            if (box) box.classList.remove('hidden');
        }
        function hideCreateError(root) {
            const box = root.querySelector('[data-tag-create-error]');
            if (box) box.classList.add('hidden');
        }

        // Build a user-added tag row matching the current Blade markup exactly.
        function buildUserTagRow(tag, usesLabel) {
            const li = document.createElement('li');
            const isActive = tag.is_active !== false;
            li.className = 'tag-row flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/60 group' + (isActive ? '' : ' opacity-60');
            li.dataset.tagId = tag.id;
            li.dataset.tagName = tag.name;
            li.dataset.tagColor = tag.color;
            li.dataset.tagIsPreset = '0';
            li.dataset.tagIsActive = isActive ? '1' : '0';
            li.innerHTML = `
                <span class="tag-name flex-1 truncate font-medium"></span>
                <span class="tag-color-code text-xs font-mono text-base-content/60 shrink-0"></span>
                <span class="tag-swatch size-5 rounded-full shrink-0 border border-base-content/10"></span>
                <label class="switch switch-primary switch-sm shrink-0" title="${isActive ? 'Active' : 'Inactive'}">
                    <input type="checkbox" data-tag-toggle ${isActive ? 'checked' : ''}>
                    <span class="switch-indicator"></span>
                </label>
                <span class="tag-status-label text-xs shrink-0 w-14 text-right ${isActive ? 'text-success' : 'text-base-content/50'}">${isActive ? 'Active' : 'Inactive'}</span>
                <div class="tag-actions flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button type="button" class="btn btn-ghost btn-xs btn-square" title="Edit" data-tag-edit>
                        <span class="icon-[tabler--edit] size-4"></span>
                    </button>
                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete" data-tag-delete>
                        <span class="icon-[tabler--trash] size-4"></span>
                    </button>
                </div>
            `;
            li.querySelector('.tag-name').textContent = tag.name;
            li.querySelector('.tag-color-code').textContent = String(tag.color).toUpperCase();
            li.querySelector('.tag-swatch').style.backgroundColor = tag.color;
            return li;
        }

        // Insert li in alphabetical order within its list (case-insensitive by name).
        function insertSorted(list, li) {
            const name = (li.dataset.tagName || '').toLowerCase();
            const siblings = Array.from(list.querySelectorAll('.tag-row'));
            const before = siblings.find(function(s) {
                return (s.dataset.tagName || '').toLowerCase().localeCompare(name) > 0;
            });
            if (before) {
                list.insertBefore(li, before);
            } else {
                list.appendChild(li);
            }
        }

        // Sync the page-level tag filter <select> on /clients with the new option (if present).
        function addToPageFilter(tag) {
            const select = document.querySelector('select#tag, select[name="tag"]');
            if (!select) return;
            // Skip if already there
            if (select.querySelector(`option[value="${tag.id}"]`)) return;
            const opt = document.createElement('option');
            opt.value = tag.id;
            opt.textContent = tag.name;
            select.appendChild(opt);
        }

        function bindDrawer(root) {
            if (root.dataset.tagDrawerBound === '1') return;
            root.dataset.tagDrawerBound = '1';

            const config = {
                storeUrl: root.dataset.tagStoreUrl,
                updateUrlTemplate: root.dataset.tagUpdateUrl,
                toggleUrlTemplate: root.dataset.tagToggleUrl || '',
                destroyUrlTemplate: root.dataset.tagDestroyUrl,
                deleteConfirm: root.dataset.tagDeleteConfirm || 'Delete this tag?',
                colors: JSON.parse(root.dataset.tagColors || '[]'),
            };

            // Create — inserts client-side; no reload, supports multi-add.
            const form = root.querySelector('[data-tag-create-form]');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    hideCreateError(root);

                    const nameInput = root.querySelector('[data-tag-create-name]');
                    const colorEl = root.querySelector('[data-tag-create-color-picker] input[name="color"]:checked');
                    const name = (nameInput?.value || '').trim();
                    const color = colorEl ? colorEl.value : config.colors[0];

                    if (!name) {
                        showCreateError(root, 'Name is required.');
                        return;
                    }

                    const btn = root.querySelector('[data-tag-create-btn]');
                    btn.disabled = true;
                    const originalBtn = btn.innerHTML;
                    btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Adding...';

                    fetch(config.storeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ name: name, color: color })
                    })
                    .then(async (r) => {
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok) {
                            throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to create tag.'));
                        }
                        return data;
                    })
                    .then((data) => {
                        const tag = data.tag;
                        if (!tag || !tag.id) {
                            throw new Error('Server did not return the created tag.');
                        }

                        // Drop the empty-state placeholder if present
                        const userList = root.querySelector('[data-tag-list="user"]');
                        const emptyState = userList?.querySelector('[data-tag-empty-state="user"]');
                        if (emptyState) emptyState.remove();

                        // Insert the new row alphabetically and bump the user count
                        const usesLabel = (root.dataset.tagUsesLabel || 'uses');
                        const li = buildUserTagRow(tag, usesLabel);
                        if (userList) insertSorted(userList, li);
                        bumpCountBy(root, 'user', +1);

                        // Patch the page-level filter select so the new tag is filterable without reload
                        addToPageFilter(tag);

                        // Reset for the next add: clear the name, keep the same color choice, refocus
                        nameInput.value = '';
                        nameInput.focus();
                    })
                    .catch((err) => {
                        showCreateError(root, err.message || 'Failed to create tag.');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = originalBtn;
                    });
                });
            }

            // Edit / Delete / Toggle — event delegation on each list (user + preset)
            root.querySelectorAll('[data-tag-list]').forEach(function(list) {
                list.addEventListener('click', function(e) {
                    const editBtn = e.target.closest('[data-tag-edit]');
                    const deleteBtn = e.target.closest('[data-tag-delete]');

                    if (editBtn) {
                        startEdit(editBtn.closest('.tag-row'), config);
                    } else if (deleteBtn) {
                        deleteRow(deleteBtn.closest('.tag-row'), config, root);
                    }
                });

                list.addEventListener('change', function(e) {
                    const toggle = e.target.closest('[data-tag-toggle]');
                    if (toggle) {
                        toggleActive(toggle, toggle.closest('.tag-row'), config);
                    }
                });
            });
        }

        function toggleActive(checkbox, row, config) {
            if (!row || !config.toggleUrlTemplate) return;
            const id = row.dataset.tagId;
            const desired = checkbox.checked;

            checkbox.disabled = true;
            fetch(config.toggleUrlTemplate.replace('__ID__', id), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ is_active: desired })
            })
            .then(async (r) => {
                const data = await r.json().catch(() => ({}));
                if (!r.ok) throw new Error(data.message || 'Toggle failed.');
                return data;
            })
            .then((data) => {
                const newActive = !!data.is_active;
                row.dataset.tagIsActive = newActive ? '1' : '0';
                row.classList.toggle('opacity-60', !newActive);
                checkbox.checked = newActive;

                const statusLabel = row.querySelector('.tag-status-label');
                if (statusLabel) {
                    statusLabel.textContent = newActive ? 'Active' : 'Inactive';
                    statusLabel.classList.toggle('text-success', newActive);
                    statusLabel.classList.toggle('text-base-content/50', !newActive);
                }
            })
            .catch((err) => {
                checkbox.checked = !desired;
                alert(err.message || 'Toggle failed.');
            })
            .finally(() => {
                checkbox.disabled = false;
            });
        }

        function startEdit(row, config) {
            if (!row || row.classList.contains('is-editing')) return;
            row.classList.add('is-editing');

            const id = row.dataset.tagId;
            const currentName = row.dataset.tagName;
            const currentColor = row.dataset.tagColor;
            const currentActive = row.dataset.tagIsActive === '1';

            const escapeAttr = (s) => String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

            const editor = document.createElement('div');
            editor.className = 'flex items-center gap-2 flex-1';
            editor.innerHTML = `
                <select class="select select-sm w-28 tag-edit-color">
                    ${config.colors.map(c => `<option value="${escapeAttr(c)}" ${c === currentColor ? 'selected' : ''}>${escapeAttr(c)}</option>`).join('')}
                </select>
                <input type="text" class="input input-sm flex-1 tag-edit-name" value="${escapeAttr(currentName)}">
                <label class="switch switch-primary switch-sm shrink-0" title="Active / Inactive">
                    <input type="checkbox" class="tag-edit-active" ${currentActive ? 'checked' : ''}>
                    <span class="switch-indicator"></span>
                </label>
                <span class="tag-edit-active-label text-xs shrink-0 w-14 text-right ${currentActive ? 'text-success' : 'text-base-content/50'}">${currentActive ? 'Active' : 'Inactive'}</span>
                <button type="button" class="btn btn-primary btn-xs tag-edit-save">Save</button>
                <button type="button" class="btn btn-ghost btn-xs tag-edit-cancel">Cancel</button>
            `;

            // Live-update the Active/Inactive label as the switch is toggled inside the editor
            const activeCheckbox = editor.querySelector('.tag-edit-active');
            const activeLabel = editor.querySelector('.tag-edit-active-label');
            activeCheckbox.addEventListener('change', function() {
                const on = activeCheckbox.checked;
                activeLabel.textContent = on ? 'Active' : 'Inactive';
                activeLabel.classList.toggle('text-success', on);
                activeLabel.classList.toggle('text-base-content/50', !on);
            });

            // Hide the row's normal content while editing; remember refs so save/cancel can restore them.
            const originalChildren = Array.from(row.children);
            originalChildren.forEach(el => el.classList.add('hidden'));
            row.appendChild(editor);

            const nameSpan = row.querySelector('.tag-name');
            const swatch = row.querySelector('.tag-swatch');
            // usageSpan/actions left as queries below — present on at least one row type
            const usageSpan = row.querySelector('.text-xs.text-base-content\\/50');
            const actions = row.querySelector('.tag-actions');

            editor.querySelector('.tag-edit-cancel').onclick = function() {
                editor.remove();
                originalChildren.forEach(el => el.classList.remove('hidden'));
                row.classList.remove('is-editing');
            };

            editor.querySelector('.tag-edit-save').onclick = function() {
                const newName = editor.querySelector('.tag-edit-name').value.trim();
                const newColor = editor.querySelector('.tag-edit-color').value;
                const newActive = editor.querySelector('.tag-edit-active').checked;
                if (!newName) return;

                const saveBtn = editor.querySelector('.tag-edit-save');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span>';

                fetch(config.updateUrlTemplate.replace('__ID__', id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: newName, color: newColor, is_active: newActive })
                })
                .then(async (r) => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(data.message || 'Update failed.');
                    return data;
                })
                .then(() => {
                    // Apply name/color/active to the row's display + data attributes
                    row.dataset.tagName = newName;
                    row.dataset.tagColor = newColor;
                    row.dataset.tagIsActive = newActive ? '1' : '0';
                    nameSpan.textContent = newName;
                    swatch.style.backgroundColor = newColor;

                    const colorCode = row.querySelector('.tag-color-code');
                    if (colorCode) colorCode.textContent = String(newColor).toUpperCase();

                    row.classList.toggle('opacity-60', !newActive);

                    // If this is a preset row, also sync the row-level toggle + status label
                    const rowToggle = row.querySelector('[data-tag-toggle]');
                    if (rowToggle) rowToggle.checked = newActive;
                    const rowStatusLabel = row.querySelector('.tag-status-label');
                    if (rowStatusLabel) {
                        rowStatusLabel.textContent = newActive ? 'Active' : 'Inactive';
                        rowStatusLabel.classList.toggle('text-success', newActive);
                        rowStatusLabel.classList.toggle('text-base-content/50', !newActive);
                    }

                    editor.remove();
                    originalChildren.forEach(el => el.classList.remove('hidden'));
                    row.classList.remove('is-editing');
                })
                .catch((err) => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = 'Save';
                    alert(err.message || 'Update failed.');
                });
            };
        }

        function deleteRow(row, config, root) {
            if (!row) return;
            const drawerId = root.dataset.tagDrawerRoot;
            const modal = document.querySelector(`[data-tag-delete-modal-for="${drawerId}"]`);
            if (!modal) {
                // No modal mounted — bail without deleting (defensive; should never happen)
                return;
            }

            const nameSlot = modal.querySelector('[data-tag-delete-name]');
            const confirmBtn = modal.querySelector('[data-tag-delete-confirm]');
            if (nameSlot) nameSlot.textContent = `"${row.dataset.tagName || ''}"`;

            // Reset any prior pending state on this modal
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<span class="icon-[tabler--trash] size-4"></span> Delete';

            // Bind a fresh confirm handler each time so it captures the right row
            confirmBtn.onclick = function() {
                const id = row.dataset.tagId;
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Deleting...';

                fetch(config.destroyUrlTemplate.replace('__ID__', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(async (r) => {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) throw new Error(data.message || 'Delete failed.');
                    return data;
                })
                .then(() => {
                    const which = row.dataset.tagIsPreset === '1' ? 'preset' : 'user';
                    row.remove();
                    bumpCountBy(root, which, -1);
                    closeDeleteModal(modal);
                })
                .catch((err) => {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<span class="icon-[tabler--trash] size-4"></span> Delete';
                    alert(err.message || 'Delete failed.');
                });
            };

            openDeleteModal(modal);
        }

        function openDeleteModal(modal) {
            modal.classList.remove('hidden');
            modal.classList.add('overlay-open');
        }
        function closeDeleteModal(modal) {
            modal.classList.remove('overlay-open');
            modal.classList.add('hidden');
        }

        // Wire the modal's Cancel/X buttons + Escape + backdrop click once per modal.
        document.querySelectorAll('[data-tag-delete-modal-for]').forEach(function(modal) {
            if (modal.dataset.tagDeleteModalBound === '1') return;
            modal.dataset.tagDeleteModalBound = '1';

            modal.querySelectorAll('[data-tag-delete-cancel]').forEach(function(btn) {
                btn.addEventListener('click', function() { closeDeleteModal(modal); });
            });

            // Click outside the dialog (on the overlay itself) closes it
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeDeleteModal(modal);
            });

            // Escape closes when this modal is open
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeDeleteModal(modal);
                }
            });
        });

        // Bind every drawer instance on the page (now and any added later)
        document.querySelectorAll('[data-tag-drawer-root]').forEach(bindDrawer);
    })();
    </script>
    @endpush
@endonce

