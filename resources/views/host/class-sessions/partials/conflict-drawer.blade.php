@php
    /**
     * Shared conflict resolution drawer.
     *
     * Expects:
     *  - $classSession            ClassSession
     *  - $conflictMessage         string|null  Display message (instructor X is not available on Y)
     *  - $availableInstructors    Collection   Instructors who work on this day
     *  - $drawerId                string       Unique drawer id (default: "conflict-drawer-{id}")
     */
    $drawerId = $drawerId ?? ('conflict-drawer-' . $classSession->id);
    $instructor = $classSession->primaryInstructor;
@endphp

{{-- Backdrop --}}
<div id="{{ $drawerId }}-backdrop"
    class="fixed inset-0 bg-black/50 z-40 hidden"
    onclick="closeConflictDrawer('{{ $drawerId }}')"></div>

{{-- Drawer --}}
<div id="{{ $drawerId }}"
    class="fixed top-0 right-0 h-full w-full sm:w-[960px] max-w-full bg-base-100 shadow-2xl z-50 transform translate-x-full transition-transform duration-300 hidden overflow-y-auto">
    <div class="flex flex-col h-full">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-base-200 bg-error text-error-content flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="icon-[tabler--alert-triangle] size-5"></span>
                <h3 class="font-bold">{{ $trans['schedule.conflict_details'] ?? 'Conflict Details' }}</h3>
            </div>
            <button type="button" class="btn btn-ghost btn-sm btn-circle !text-white" onclick="closeConflictDrawer('{{ $drawerId }}')">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 p-5 space-y-5">
            {{-- Session summary (compact) --}}
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 px-3 py-2 bg-base-200/50 rounded-lg text-sm">
                <span class="font-semibold">{{ $classSession->title ?: ($classSession->classPlan->name ?? 'Session') }}</span>
                <span class="flex items-center gap-1 text-base-content/70">
                    <span class="icon-[tabler--calendar] size-4"></span>
                    {{ $classSession->start_time->format('l, M j, Y') }}
                </span>
                <span class="flex items-center gap-1 text-base-content/70">
                    <span class="icon-[tabler--clock] size-4"></span>
                    {{ $classSession->start_time->format('g:i A') }} - {{ $classSession->end_time->format('g:i A') }}
                </span>
            </div>

            {{-- Solution: reassign instructor --}}
            <div>
                <h4 class="font-semibold mb-2">{{ $trans['schedule.suggested_solution'] ?? 'Suggested Solution' }}</h4>
                <p class="text-sm text-base-content/70 mb-3">
                    {{ $trans['schedule.reassign_instructor_help'] ?? 'Reassign this session to another instructor who is available on this day.' }}
                </p>

                @if($availableInstructors->isEmpty())
                    <div class="alert alert-warning">
                        <span class="icon-[tabler--info-circle] size-5"></span>
                        <p class="text-sm">
                            {{ $trans['schedule.no_available_instructors'] ?? 'No other instructors are available on this day.' }}
                        </p>
                    </div>
                @else
                    <form action="{{ route('class-sessions.reassign-instructor', $classSession) }}" method="POST" class="space-y-3"
                        data-conflict-form
                        data-session-date="{{ $classSession->start_time->format('Y-m-d') }}"
                        data-session-start="{{ $classSession->start_time->format('g:i A') }}"
                        data-session-end="{{ $classSession->end_time->format('g:i A') }}">
                        @csrf @method('PATCH')

                        <div>
                            <label class="label-text" for="{{ $drawerId }}-instructor">
                                {{ $trans['schedule.choose_new_instructor'] ?? 'Choose new instructor' }}
                            </label>
                            <x-studio-select
                                name="primary_instructor_id"
                                :id="$drawerId . '-instructor'"
                                :options="$availableInstructors->pluck('name', 'id')->toArray()"
                                :placeholder="$trans['common.select'] ?? 'Select...'"
                                required
                            />
                        </div>

                        {{-- Availability panel (rendered below input once an instructor is picked) --}}
                        <div data-avail-placeholder class="text-center py-6 border-2 border-dashed border-base-300 rounded-lg">
                            <span class="icon-[tabler--user] size-8 text-base-content/20 mx-auto mb-1"></span>
                            <p class="text-base-content/50 text-sm">{{ $trans['schedule.pick_instructor_to_see_availability'] ?? 'Select an instructor to see their availability' }}</p>
                        </div>

                        <div data-avail-loading class="hidden text-center py-6">
                            <span class="loading loading-spinner loading-md text-primary"></span>
                            <p class="text-base-content/50 mt-2 text-sm">Loading availability...</p>
                        </div>

                        <div data-avail-panel class="hidden space-y-3 border border-base-200 rounded-lg p-3">
                            <div class="flex items-center gap-3 bg-base-200/50 rounded-lg p-2">
                                <div class="size-9 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold" data-avail-initials>?</div>
                                <div>
                                    <div class="font-semibold text-sm" data-avail-name>Instructor</div>
                                    <div class="text-xs text-base-content/60" data-avail-subtitle></div>
                                </div>
                            </div>

                            <div data-avail-hours-wrap class="hidden">
                                <div class="text-xs font-medium text-base-content/60 mb-1">Available Hours ({{ $classSession->start_time->format('l') }})</div>
                                <div class="flex items-center gap-2 p-2 bg-base-200/50 rounded-lg text-sm">
                                    <span class="icon-[tabler--clock] size-4 text-primary"></span>
                                    <span data-avail-hours>—</span>
                                </div>
                            </div>

                            <div>
                                <div class="text-xs font-medium text-base-content/60 mb-1">Working Days</div>
                                <div class="flex gap-1.5" data-avail-days></div>
                            </div>

                            <div data-avail-existing-wrap class="hidden">
                                <div class="text-xs font-medium text-base-content/60 mb-1">Other Sessions That Day</div>
                                <ul class="text-sm space-y-1" data-avail-existing></ul>
                            </div>

                            <div data-avail-fit class="text-xs font-medium"></div>
                        </div>

                        <button type="submit" class="btn btn-primary w-full">
                            <span class="icon-[tabler--check] size-4"></span>
                            {{ $trans['schedule.reassign_and_resolve'] ?? 'Reassign & Resolve Conflict' }}
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-base-200">
            <button type="button" class="btn btn-ghost w-full" onclick="closeConflictDrawer('{{ $drawerId }}')">
                {{ $trans['btn.close'] ?? 'Close' }}
            </button>
        </div>
    </div>
</div>

@once
<script>
function openConflictDrawer(id) {
    var drawer = document.getElementById(id);
    var backdrop = document.getElementById(id + '-backdrop');
    if (!drawer) return;
    drawer.classList.remove('hidden');
    if (backdrop) backdrop.classList.remove('hidden');
    requestAnimationFrame(function() {
        drawer.classList.remove('translate-x-full');
    });
    document.body.style.overflow = 'hidden';

    // Observe the select for value changes (advance-select may not fire change reliably)
    var form = drawer.querySelector('form[data-conflict-form]');
    if (form && !form.dataset.observerAttached) {
        var sel = form.querySelector('select[name="primary_instructor_id"]');
        if (sel) {
            var lastValue = sel.value;
            var observer = new MutationObserver(function () {
                if (sel.value !== lastValue) {
                    lastValue = sel.value;
                    fetchConflictAvailability(form, sel.value);
                }
            });
            observer.observe(sel, { attributes: true, childList: true, subtree: true });
            form.dataset.observerAttached = '1';
        }
    }
}

function closeConflictDrawer(id) {
    var drawer = document.getElementById(id);
    var backdrop = document.getElementById(id + '-backdrop');
    if (!drawer) return;
    drawer.classList.add('translate-x-full');
    if (backdrop) backdrop.classList.add('hidden');
    setTimeout(function() { drawer.classList.add('hidden'); }, 300);
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[id^="conflict-drawer-"]').forEach(function(el) {
        if (!el.id.endsWith('-backdrop') && !el.classList.contains('hidden')) {
            closeConflictDrawer(el.id);
        }
    });
});

// Availability fetch for the conflict drawer form
function parseTimeToMinutes(str) {
    if (!str) return null;
    var m = String(str).trim().match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
    if (!m) return null;
    var h = parseInt(m[1], 10) % 12;
    if (m[3].toUpperCase() === 'PM') h += 12;
    return h * 60 + parseInt(m[2], 10);
}

function renderConflictAvailability(form, data) {
    var placeholder = form.querySelector('[data-avail-placeholder]');
    var loading = form.querySelector('[data-avail-loading]');
    var panel = form.querySelector('[data-avail-panel]');
    placeholder.classList.add('hidden');
    loading.classList.add('hidden');
    panel.classList.remove('hidden');

    var name = (data.instructor && data.instructor.name) || 'Instructor';
    var initials = (data.instructor && data.instructor.initials) || name.substring(0, 2).toUpperCase();
    form.querySelector('[data-avail-name]').textContent = name;
    form.querySelector('[data-avail-initials]').textContent = initials;

    var dayLabel = data.day_name || '';
    var dayLetters = ['S','M','T','W','T','F','S'];
    var daysWrap = form.querySelector('[data-avail-days]');
    var hoursWrap = form.querySelector('[data-avail-hours-wrap]');
    var hoursEl = form.querySelector('[data-avail-hours]');
    var existingWrap = form.querySelector('[data-avail-existing-wrap]');
    var existingList = form.querySelector('[data-avail-existing]');
    var fitEl = form.querySelector('[data-avail-fit]');

    // Reset
    daysWrap.innerHTML = '';
    existingList.innerHTML = '';
    fitEl.className = 'text-xs font-medium';
    fitEl.textContent = '';

    // Instructor has no working_days / availability_by_day / availability_hours configured
    if (!data.has_configured_availability) {
        form.querySelector('[data-avail-subtitle]').textContent = 'Availability not configured';

        hoursWrap.classList.remove('hidden');
        hoursEl.innerHTML = '<span class="text-warning">Not configured</span>';

        // All days greyed
        for (var i = 0; i < 7; i++) {
            var chip = document.createElement('span');
            chip.className = 'size-7 rounded text-xs font-medium flex items-center justify-center bg-base-200 text-base-content/40';
            chip.textContent = dayLetters[i];
            daysWrap.appendChild(chip);
        }

        existingWrap.classList.add('hidden');

        fitEl.classList.add('text-warning');
        fitEl.innerHTML = '<span class="icon-[tabler--alert-triangle] size-3.5 inline align-text-bottom mr-1"></span>'
            + 'No working days or hours configured for this instructor.';
        return;
    }

    var works = !!data.works_today;
    form.querySelector('[data-avail-subtitle]').textContent = works
        ? ('Available on ' + dayLabel)
        : ('Not available on ' + dayLabel);

    // Hours
    if (works) {
        hoursWrap.classList.remove('hidden');
        if (data.availability && data.availability.from && data.availability.to) {
            hoursEl.textContent = data.availability.from + ' – ' + data.availability.to;
        } else {
            hoursEl.textContent = 'All day';
        }
    } else {
        hoursWrap.classList.remove('hidden');
        hoursEl.innerHTML = '<span class="text-error">Off on ' + dayLabel + '</span>';
    }

    // Working days chips
    var working = data.working_days || [];
    var sessionDow = new Date(form.dataset.sessionDate + 'T00:00:00').getDay();
    for (var j = 0; j < 7; j++) {
        var c = document.createElement('span');
        var isWorking = !!working[j];
        var isSessionDay = j === sessionDow;
        var baseClass = 'size-7 rounded text-xs font-medium flex items-center justify-center';
        if (isSessionDay) {
            c.className = baseClass + ' ' + (isWorking
                ? 'bg-primary text-primary-content ring-2 ring-primary ring-offset-1'
                : 'bg-error/15 text-error ring-2 ring-error ring-offset-1');
        } else {
            c.className = baseClass + ' ' + (isWorking
                ? 'bg-success/15 text-success'
                : 'bg-base-200 text-base-content/40');
        }
        c.textContent = dayLetters[j];
        daysWrap.appendChild(c);
    }

    // Other sessions that day
    if (data.existing_sessions && data.existing_sessions.length) {
        existingWrap.classList.remove('hidden');
        data.existing_sessions.forEach(function (s) {
            var li = document.createElement('li');
            li.className = 'flex items-center gap-2';
            li.innerHTML = '<span class="icon-[tabler--clock] size-3.5 text-base-content/50"></span>'
                + '<span class="text-base-content/70">' + s.time + '</span>'
                + '<span class="text-base-content/50">·</span>'
                + '<span>' + s.title + '</span>';
            existingList.appendChild(li);
        });
    } else {
        existingWrap.classList.add('hidden');
    }

    // Fit check vs. session time
    if (!works) {
        fitEl.classList.add('text-error');
        fitEl.textContent = 'Instructor is not available on ' + dayLabel + '.';
    } else if (data.availability && data.availability.from && data.availability.to) {
        var availFrom = parseTimeToMinutes(data.availability.from);
        var availTo = parseTimeToMinutes(data.availability.to);
        var sessFrom = parseTimeToMinutes(form.dataset.sessionStart);
        var sessTo = parseTimeToMinutes(form.dataset.sessionEnd);
        if (availFrom != null && availTo != null && sessFrom != null && sessTo != null) {
            if (sessFrom >= availFrom && sessTo <= availTo) {
                fitEl.classList.add('text-success');
                fitEl.textContent = 'Session time fits within this instructor\'s hours.';
            } else {
                fitEl.classList.add('text-warning');
                fitEl.textContent = 'Session time is outside this instructor\'s working hours.';
            }
        }
    } else {
        fitEl.classList.add('text-success');
        fitEl.textContent = 'Instructor is available all day on ' + dayLabel + '.';
    }
}

function fetchConflictAvailability(form, instructorId) {
    var placeholder = form.querySelector('[data-avail-placeholder]');
    var loading = form.querySelector('[data-avail-loading]');
    var panel = form.querySelector('[data-avail-panel]');

    if (!instructorId) {
        placeholder.classList.remove('hidden');
        loading.classList.add('hidden');
        panel.classList.add('hidden');
        return;
    }

    placeholder.classList.add('hidden');
    panel.classList.add('hidden');
    loading.classList.remove('hidden');

    var url = '/walk-in/instructor-availability?instructor_id=' + encodeURIComponent(instructorId)
        + '&date=' + encodeURIComponent(form.dataset.sessionDate);

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (data) { renderConflictAvailability(form, data); })
        .catch(function () {
            loading.classList.add('hidden');
            placeholder.classList.remove('hidden');
        });
}

document.addEventListener('change', function (e) {
    var select = e.target.closest('select[name="primary_instructor_id"]');
    if (!select) return;
    var form = select.closest('form[data-conflict-form]');
    if (!form) return;
    fetchConflictAvailability(form, select.value);
});
</script>
@endonce
