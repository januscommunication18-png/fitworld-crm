@extends('layouts.dashboard')

@section('title', $trans['setup.page_title'] ?? 'Complete Your Studio Setup')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Compact Header with Progress --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">
                @if($host->studio_name)
                    {{ str_replace(':studio_name', $host->studio_name, $trans['setup.welcome'] ?? 'Welcome to :studio_name!') }}
                @else
                    {{ $trans['setup.welcome_default'] ?? 'Welcome to Your Studio!' }}
                @endif
            </h1>
            <p class="text-base-content/60 text-sm">{{ $trans['setup.subtitle'] ?? 'Complete setup to unlock all features' }}</p>
        </div>
        <div class="flex items-center gap-4">
            {{-- Progress Ring - Compact --}}
            <div class="relative inline-flex items-center justify-center">
                <svg class="w-16 h-16 transform -rotate-90">
                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none" class="text-base-300"/>
                    <circle cx="32" cy="32" r="28" stroke="currentColor" stroke-width="4" fill="none"
                        class="text-primary transition-all duration-500"
                        stroke-dasharray="{{ 2 * 3.14159 * 28 }}"
                        stroke-dashoffset="{{ 2 * 3.14159 * 28 * (1 - $progress / 100) }}"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-lg font-bold">{{ $progress }}%</span>
                </div>
            </div>
            <div class="text-sm">
                <div class="font-semibold">{{ $completedCount }}/{{ $totalCount }}</div>
                <div class="text-base-content/50">{{ $trans['setup.completed'] ?? 'completed' }}</div>
            </div>
        </div>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Left: Checklist (3 cols) --}}
        <div class="lg:col-span-3">
            <div class="card bg-base-100">
                <div class="card-body p-4">
                    <h2 class="font-semibold mb-4 flex items-center gap-2">
                        <span class="icon-[tabler--list-check] size-5 text-primary"></span>
                        {{ $trans['setup.checklist_title'] ?? 'Setup Checklist' }}
                    </h2>

                    <div class="space-y-2">
                        {{-- 1. Verify Email Address --}}
                        @php $item = $checklist['verify_email']; @endphp
                        <div class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-warning/20 bg-warning/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-warning/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--mail] size-4 text-warning"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.verify_email'] ?? 'Verify Email Address' }}</div>
                                <div class="text-xs text-base-content/50 truncate">
                                    @if($item['completed'])
                                        {{ $trans['setup.email_verified'] ?? 'Email verified' }}
                                    @else
                                        {{ $trans['setup.check_inbox'] ?? 'Check your inbox for verification link' }}
                                    @endif
                                </div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <form action="{{ route('verification.send') }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <span class="icon-[tabler--send] size-4"></span>
                                        {{ $trans['setup.resend'] ?? 'Resend' }}
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- 2. Verify Phone Number (Optional) --}}
                        @php $item = $checklist['verify_phone']; @endphp
                        <div class="flex items-center gap-3 p-3 rounded-lg border transition-all cursor-pointer {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}"
                             @if(!$item['completed']) onclick="openPhoneModal()" @endif>
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--phone] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">
                                    {{ $trans['setup.verify_phone'] ?? 'Verify Phone Number' }}
                                    <span class="badge badge-ghost badge-xs ml-1">{{ $trans['setup.optional'] ?? 'Optional' }}</span>
                                </div>
                                <div class="text-xs text-base-content/50 truncate">
                                    @if($item['completed'])
                                        {{ $trans['setup.phone_verified'] ?? 'Phone number verified' }}
                                    @else
                                        {{ $trans['setup.phone_desc'] ?? 'Verify via SMS for added security' }}
                                    @endif
                                </div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <button type="button" class="btn btn-soft btn-sm" onclick="event.stopPropagation(); openPhoneModal();">
                                    <span class="icon-[tabler--message] size-4"></span>
                                    {{ $trans['setup.send_sms'] ?? 'Send SMS' }}
                                </button>
                            @endif
                        </div>

                        {{-- 3. Studio Information (Expandable Accordion) --}}
                        @php
                            $item = $checklist['studio_info'];
                            $selectedCategories = $host->studio_categories ?? [];
                            if (is_string($selectedCategories)) {
                                $selectedCategories = json_decode($selectedCategories, true) ?? [];
                            }
                            $allCategoriesList = [
                                'Yoga (Hatha, Vinyasa, Power, Yin, Restorative)', 'Pilates (Mat / Reformer)', 'Meditation / Mindfulness', 'Breathwork', 'Tai Chi', 'Qigong', 'Stretching / Mobility', 'Barre',
                                'Strength Training', 'Functional Training', 'CrossFit', 'Weightlifting (Olympic)', 'Powerlifting', 'Bodyweight Training (Calisthenics)', 'Bootcamp', 'Circuit Training',
                                'HIIT (High-Intensity Interval Training)', 'Indoor Cycling / Spin', 'Running / Treadmill', 'Rowing', 'Step Aerobics', 'Cardio Kickboxing',
                                'Boxing', 'Kickboxing', 'Muay Thai', 'MMA (Mixed Martial Arts)', 'Brazilian Jiu-Jitsu (BJJ)', 'Karate', 'Taekwondo', 'Self-Defense',
                                'Zumba', 'Dance Fitness', 'Hip Hop Dance', 'Ballet Fitness', 'Jazzercise',
                                'Open Gym', 'Personal Training', 'Small Group Training', 'Beginner Fitness', 'Senior Fitness', 'Youth Fitness',
                                'Prenatal / Postnatal Fitness', 'Rehab / Physical Therapy', 'Injury Recovery', 'Adaptive Fitness', 'EMS (Electro Muscle Stimulation)', 'Sports Performance Training', 'Athlete Conditioning',
                                'Recovery Sessions', 'Foam Rolling', 'Mobility & Flexibility', 'Sauna / Cold Therapy Sessions', 'Relaxation Therapy',
                                'Outdoor Bootcamp', 'Hiking Fitness', 'Trail Running', 'Cycling (Outdoor)', 'Adventure Fitness',
                            ];
                            $customCategoriesSetup = array_diff($selectedCategories, $allCategoriesList);
                            $supportedLanguages = [
                                'en' => ['name' => 'English', 'flag' => '🇺🇸'],
                                'es' => ['name' => 'Spanish', 'flag' => '🇪🇸'],
                                'fr' => ['name' => 'French', 'flag' => '🇫🇷'],
                                'de' => ['name' => 'German', 'flag' => '🇩🇪'],
                                'pt' => ['name' => 'Portuguese', 'flag' => '🇧🇷'],
                                'it' => ['name' => 'Italian', 'flag' => '🇮🇹'],
                            ];
                            $currencies = [
                                'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
                                'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
                                'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
                                'EUR' => ['symbol' => '€', 'name' => 'Euro'],
                                'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
                                'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
                            ];
                            $bookingSettings = $host->booking_settings ?? [];
                        @endphp
                        <div class="rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300' }}">
                            {{-- Accordion Header --}}
                            <div class="flex items-start gap-3 p-3 cursor-pointer" onclick="toggleStudioInfoAccordion()">
                                @if($item['completed'])
                                    <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="icon-[tabler--check] size-4 text-success"></span>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <span class="icon-[tabler--building-store] size-4 text-base-content/50"></span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">{{ $trans['setup.studio_info'] ?? 'Studio Information' }}</div>
                                    <div class="text-xs text-base-content/50 mt-1">
                                        <div class="flex flex-wrap gap-x-2 gap-y-1">
                                            <span>Studio Name</span><span class="text-base-content/30">•</span>
                                            <span>Structure</span><span class="text-base-content/30">•</span>
                                            <span>Sub-domain</span><span class="text-base-content/30">•</span>
                                            <span>Categories</span><span class="text-base-content/30">•</span>
                                            <span>Language</span><span class="text-base-content/30">•</span>
                                            <span>Currency</span><span class="text-base-content/30">•</span>
                                            <span>Cancellation Policy</span>
                                        </div>
                                    </div>
                                </div>
                                @if($item['completed'])
                                    <span class="badge badge-success badge-soft badge-sm flex-shrink-0">{{ $trans['setup.done'] ?? 'Done' }}</span>
                                @endif
                                <span id="studio-info-chevron" class="icon-[tabler--chevron-down] size-5 text-base-content/30 flex-shrink-0 transition-transform"></span>
                            </div>

                            {{-- Accordion Content (Form) --}}
                            <div id="studio-info-form-container" class="hidden border-t border-base-200">
                                <form id="studio-info-form" class="p-4 space-y-4">
                                    @csrf
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Studio Name --}}
                                        <div>
                                            <label class="label-text text-sm font-medium" for="setup_studio_name">Studio Name <span class="text-error">*</span></label>
                                            <input id="setup_studio_name" type="text" class="input input-bordered w-full mt-1" value="{{ $host->studio_name ?? '' }}" placeholder="e.g. Sunrise Yoga Studio" required />
                                        </div>

                                        {{-- Studio Structure --}}
                                        <div>
                                            <label class="label-text text-sm font-medium" for="setup_studio_structure">Studio Structure <span class="text-error">*</span></label>
                                            <select id="setup_studio_structure" class="select select-bordered w-full mt-1" required>
                                                <option value="">Select structure...</option>
                                                <option value="solo" {{ ($host->studio_structure ?? '') == 'solo' ? 'selected' : '' }}>Solo (Just me)</option>
                                                <option value="team" {{ ($host->studio_structure ?? '') == 'team' ? 'selected' : '' }}>With a Team (Staff members)</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Sub-domain --}}
                                    <div>
                                        <label class="label-text text-sm font-medium" for="setup_subdomain">Sub-domain Name <span class="text-error">*</span></label>
                                        <div class="join w-full mt-1">
                                            <input id="setup_subdomain" type="text" class="input input-bordered join-item flex-1 {{ $host->subdomain ? 'bg-base-200 cursor-not-allowed' : '' }}" value="{{ $host->subdomain ?? '' }}" {{ $host->subdomain ? 'readonly' : 'required' }} placeholder="yourstudio" />
                                            <span class="btn btn-soft join-item pointer-events-none">.{{ config('app.booking_domain', 'fitcrm.biz') }}</span>
                                        </div>
                                        @if($host->subdomain)
                                            <p class="text-xs text-base-content/50 mt-1">Subdomain cannot be changed after setup</p>
                                        @endif
                                    </div>

                                    {{-- Studio Categories (Searchable Multiselect) --}}
                                    <div>
                                        <label class="label-text text-sm font-medium">Studio Categories <span class="text-error">*</span></label>
                                        <p class="text-xs text-base-content/50 mb-2">Select all categories that apply to your studio</p>

                                        {{-- Search Input --}}
                                        <div class="relative mb-2">
                                            <span class="icon-[tabler--search] size-4 absolute left-3 top-1/2 -translate-y-1/2 text-base-content/40"></span>
                                            <input type="text" id="setup-category-search" class="input input-bordered input-sm w-full pl-9" placeholder="Search categories..." oninput="filterSetupCategories(this.value)">
                                        </div>

                                        {{-- Selected Tags --}}
                                        <div id="setup-selected-tags" class="flex flex-wrap gap-1 mb-2 {{ count($selectedCategories) == 0 ? 'hidden' : '' }}">
                                            @foreach(array_intersect($selectedCategories, $allCategoriesList) as $cat)
                                            <span class="badge badge-primary badge-xs gap-1" data-cat="{{ $cat }}">{{ Str::limit($cat, 20) }} <button type="button" onclick="toggleSetupCategory('{{ addslashes($cat) }}')" class="hover:opacity-70"><span class="icon-[tabler--x] size-3"></span></button></span>
                                            @endforeach
                                        </div>

                                        {{-- Categories List --}}
                                        <div id="setup-category-list" class="max-h-32 overflow-y-auto border border-base-200 rounded-lg p-2 space-y-0.5">
                                            @foreach($allCategoriesList as $option)
                                            <label class="setup-cat-item flex items-center gap-2 cursor-pointer p-1 hover:bg-base-200 rounded text-xs" data-search="{{ strtolower($option) }}">
                                                <input type="checkbox" name="setup_studio_categories[]" value="{{ $option }}" class="checkbox checkbox-primary checkbox-xs setup-category-checkbox" {{ in_array($option, $selectedCategories) ? 'checked' : '' }} onchange="onSetupCategoryChange()" />
                                                <span>{{ $option }}</span>
                                            </label>
                                            @endforeach
                                        </div>

                                        {{-- Others Option --}}
                                        <div class="mt-2 pt-2 border-t border-base-200">
                                            <label class="flex items-center gap-2 cursor-pointer text-xs">
                                                <input type="checkbox" id="setup-others-checkbox" class="checkbox checkbox-primary checkbox-xs" {{ count($customCategoriesSetup) > 0 ? 'checked' : '' }} onchange="toggleSetupOthers()">
                                                <span class="font-medium">Others (Add custom)</span>
                                            </label>
                                        </div>
                                        <div id="setup-custom-section" class="{{ count($customCategoriesSetup) == 0 ? 'hidden' : '' }} mt-2">
                                            <textarea id="setup-custom-categories" class="textarea textarea-bordered textarea-sm w-full" rows="2" placeholder="One per line...">{{ implode("\n", $customCategoriesSetup) }}</textarea>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {{-- Default Language --}}
                                        <div>
                                            <label class="label-text text-sm font-medium" for="setup_default_language">Default Language <span class="text-error">*</span></label>
                                            <select id="setup_default_language" class="select select-bordered w-full mt-1" required>
                                                <option value="">Select language...</option>
                                                @foreach($supportedLanguages as $code => $info)
                                                    <option value="{{ $code }}" {{ ($host->default_language_app ?? '') == $code ? 'selected' : '' }}>{{ $info['flag'] }} {{ $info['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Default Currency --}}
                                        <div>
                                            <label class="label-text text-sm font-medium" for="setup_default_currency">Default Currency <span class="text-error">*</span></label>
                                            <select id="setup_default_currency" class="select select-bordered w-full mt-1" required>
                                                <option value="">Select currency...</option>
                                                @foreach($currencies as $code => $info)
                                                    <option value="{{ $code }}" {{ ($host->default_currency ?? '') == $code ? 'selected' : '' }}>{{ $info['symbol'] }} {{ $code }} - {{ $info['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Cancellation Policy --}}
                                    <div class="p-3 bg-base-200/50 rounded-lg">
                                        <label class="label-text text-sm font-medium mb-2 block">Cancellation Policy <span class="text-error">*</span></label>
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="checkbox" id="setup_allow_cancellations" class="checkbox checkbox-primary checkbox-sm" {{ isset($bookingSettings['allow_cancellations']) && $bookingSettings['allow_cancellations'] ? 'checked' : '' }} />
                                                <span class="text-sm">Allow clients to cancel bookings</span>
                                            </label>
                                            <div id="cancellation-options" class="{{ isset($bookingSettings['allow_cancellations']) && $bookingSettings['allow_cancellations'] ? '' : 'hidden' }} pl-7 space-y-2">
                                                <div class="flex items-center gap-2">
                                                    <label class="text-xs text-base-content/70">Cancellation deadline:</label>
                                                    <select id="setup_cancellation_hours" class="select select-bordered select-xs w-24">
                                                        @foreach([2, 4, 6, 12, 24, 48, 72] as $hours)
                                                            <option value="{{ $hours }}" {{ ($bookingSettings['cancellation_deadline_hours'] ?? 24) == $hours ? 'selected' : '' }}>{{ $hours }}h</option>
                                                        @endforeach
                                                    </select>
                                                    <span class="text-xs text-base-content/50">before class</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Error Message --}}
                                    <div id="studio-info-error" class="alert alert-error hidden">
                                        <span class="icon-[tabler--alert-circle] size-5"></span>
                                        <span id="studio-info-error-text"></span>
                                    </div>

                                    {{-- Save Button --}}
                                    <div class="flex justify-end pt-2">
                                        <button type="button" id="save-studio-info-btn" class="btn btn-primary" onclick="saveStudioInfo()">
                                            <span class="icon-[tabler--device-floppy] size-4"></span>
                                            Save Studio Information
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- 4. Location Information --}}
                        @php $item = $checklist['location']; @endphp
                        <a href="{{ route('settings.locations.index') }}"
                           class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300 hover:border-primary hover:bg-primary/5' }}">
                            @if($item['completed'])
                                <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--check] size-4 text-success"></span>
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                    <span class="icon-[tabler--map-pin] size-4 text-base-content/50"></span>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-sm">{{ $trans['setup.location'] ?? 'Location Information' }}</div>
                                <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.location_desc'] ?? 'Address & room configuration' }}</div>
                            </div>
                            @if($item['completed'])
                                <span class="badge badge-success badge-soft badge-sm">{{ $trans['setup.done'] ?? 'Done' }}</span>
                            @else
                                <span class="icon-[tabler--chevron-right] size-5 text-base-content/30"></span>
                            @endif
                        </a>

                        {{-- 5. Staff Member (Expandable Accordion) --}}
                        @php $item = $checklist['staff_member']; @endphp
                        <div class="rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300' }}">
                            {{-- Accordion Header --}}
                            <div class="flex items-center gap-3 p-3 cursor-pointer" onclick="toggleStaffAccordion()">
                                @if($item['completed'])
                                    <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                        <span class="icon-[tabler--check] size-4 text-success"></span>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                        <span class="icon-[tabler--users] size-4 text-base-content/50"></span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">{{ $trans['setup.staff_member'] ?? 'Staff Member' }}</div>
                                    <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.staff_member_desc'] ?? 'Add team members with availability' }}</div>
                                </div>
                                @if($item['completed'])
                                    <span class="badge badge-success badge-soft badge-sm flex-shrink-0">{{ $trans['setup.done'] ?? 'Done' }}</span>
                                @endif
                                <span id="staff-chevron" class="icon-[tabler--chevron-down] size-5 text-base-content/30 flex-shrink-0 transition-transform"></span>
                            </div>

                            {{-- Accordion Content --}}
                            <div id="staff-form-container" class="hidden border-t border-base-200">
                                <div class="p-4 space-y-4">
                                    {{-- Existing Team Members --}}
                                    <div>
                                        <label class="label-text text-sm font-medium mb-2 block">Current Team Members</label>
                                        <div class="space-y-2 max-h-32 overflow-y-auto">
                                            @forelse($teamMembers ?? [] as $member)
                                                <div class="flex items-center gap-3 p-2 bg-base-200/50 rounded-lg">
                                                    <div class="avatar placeholder">
                                                        <div class="w-8 h-8 rounded-full bg-primary/20 text-primary">
                                                            <span class="text-xs font-medium">{{ strtoupper(substr($member['name'], 0, 2)) }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="text-sm font-medium truncate">{{ $member['name'] }}</div>
                                                        <div class="text-xs text-base-content/50 truncate">{{ $member['email'] }}</div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        @if($member['is_owner'])
                                                            <span class="badge badge-primary badge-soft badge-xs">Owner</span>
                                                        @else
                                                            <span class="badge badge-ghost badge-xs">{{ ucfirst($member['role']) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-sm text-base-content/50 text-center py-2">No team members yet</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    {{-- Quick Add Member Form --}}
                                    <div class="border-t border-base-200 pt-4">
                                        <label class="label-text text-sm font-medium mb-3 block flex items-center gap-2">
                                            <span class="icon-[tabler--user-plus] size-4"></span>
                                            Quick Add Member
                                        </label>
                                        <form id="quick-add-member-form" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                {{-- Name --}}
                                                <div>
                                                    <label class="label-text text-xs" for="member_name">Name <span class="text-error">*</span></label>
                                                    <input id="member_name" type="text" class="input input-bordered input-sm w-full mt-1" placeholder="John Doe" required />
                                                </div>

                                                {{-- Email --}}
                                                <div>
                                                    <label class="label-text text-xs" for="member_email">Email Address <span class="text-error">*</span></label>
                                                    <input id="member_email" type="email" class="input input-bordered input-sm w-full mt-1" placeholder="john@example.com" required />
                                                </div>
                                            </div>

                                            {{-- Role --}}
                                            <div>
                                                <label class="label-text text-xs" for="member_role">Role <span class="text-error">*</span></label>
                                                <select id="member_role" class="select select-bordered select-sm w-full mt-1" required>
                                                    <option value="">Select a role...</option>
                                                    <option value="admin">Admin - Full system access</option>
                                                    <option value="manager">Manager - Manage operations</option>
                                                    <option value="staff">Staff - Limited access</option>
                                                    <option value="instructor">Instructor - Teaching & classes</option>
                                                </select>
                                            </div>

                                            {{-- Error Message --}}
                                            <div id="member-error" class="alert alert-error alert-sm hidden">
                                                <span class="icon-[tabler--alert-circle] size-4"></span>
                                                <span id="member-error-text" class="text-sm"></span>
                                            </div>

                                            {{-- Success Message --}}
                                            <div id="member-success" class="alert alert-success alert-sm hidden">
                                                <span class="icon-[tabler--check] size-4"></span>
                                                <span id="member-success-text" class="text-sm"></span>
                                            </div>

                                            {{-- Submit Button --}}
                                            <div class="flex justify-end">
                                                <button type="button" id="send-invite-btn" class="btn btn-primary btn-sm" onclick="sendMemberInvite()">
                                                    <span class="icon-[tabler--send] size-4"></span>
                                                    Send an Invite
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 6. Booking Page (Expandable Accordion) --}}
                        @php
                            $item = $checklist['booking_page'];
                            $bookingPageStatus = $host->booking_page_status ?? 'draft';
                            $bookingSettings = $host->booking_settings ?? [];
                            $bookingDomain = config('app.booking_domain', 'projectfit.com');
                            $bookingScheme = app()->environment('local') ? 'http' : 'https';
                            $bookingPort = app()->environment('local') ? ':8888' : '';
                            $bookingUrl = "{$bookingScheme}://{$host->subdomain}.{$bookingDomain}{$bookingPort}";
                        @endphp
                        <div class="rounded-lg border transition-all {{ $item['completed'] ? 'bg-success/5 border-success/20' : 'border-base-300' }}">
                            {{-- Accordion Header --}}
                            <div class="flex items-center gap-3 p-3 cursor-pointer" onclick="toggleBookingPageAccordion()">
                                @if($item['completed'])
                                    <div class="w-8 h-8 rounded-full bg-success/20 flex items-center justify-center flex-shrink-0">
                                        <span class="icon-[tabler--check] size-4 text-success"></span>
                                    </div>
                                @else
                                    <div class="w-8 h-8 rounded-full bg-base-200 flex items-center justify-center flex-shrink-0">
                                        <span class="icon-[tabler--calendar-event] size-4 text-base-content/50"></span>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm">{{ $trans['setup.booking_page'] ?? 'Booking Page' }}</div>
                                    <div class="text-xs text-base-content/50 truncate">{{ $trans['setup.booking_page_desc'] ?? 'Publish your booking page to start accepting bookings' }}</div>
                                </div>
                                @if($item['completed'])
                                    <span class="badge badge-success badge-soft badge-sm flex-shrink-0">{{ $trans['setup.done'] ?? 'Done' }}</span>
                                @endif
                                <span id="booking-chevron" class="icon-[tabler--chevron-down] size-5 text-base-content/30 flex-shrink-0 transition-transform"></span>
                            </div>

                            {{-- Accordion Content --}}
                            <div id="booking-form-container" class="hidden border-t border-base-200">
                                <div class="p-4 space-y-4">
                                    {{-- Current Status --}}
                                    <div class="p-3 rounded-lg {{ $bookingPageStatus === 'published' ? 'bg-success/10 border border-success/20' : 'bg-warning/10 border border-warning/20' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                @if($bookingPageStatus === 'published')
                                                    <span class="icon-[tabler--world] size-5 text-success"></span>
                                                    <div>
                                                        <span class="font-medium text-sm text-success">Page Published</span>
                                                        <p class="text-xs text-base-content/60">Your booking page is live</p>
                                                    </div>
                                                @else
                                                    <span class="icon-[tabler--eye-off] size-5 text-warning"></span>
                                                    <div>
                                                        <span class="font-medium text-sm text-warning">Page Draft</span>
                                                        <p class="text-xs text-base-content/60">Not visible to public</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Publish Toggle --}}
                                    <form id="quick-booking-page-form" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="label-text text-sm font-medium mb-2 block">Page Status <span class="text-error">*</span></label>
                                            <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-lg">
                                                <span class="text-sm text-base-content/60">Draft</span>
                                                <label class="switch switch-success">
                                                    <input type="checkbox" id="setup_booking_status" {{ $bookingPageStatus === 'published' ? 'checked' : '' }} />
                                                    <span class="switch-indicator"></span>
                                                </label>
                                                <span class="text-sm text-base-content/60">Published</span>
                                            </div>
                                            <p class="text-xs text-base-content/50 mt-1">Publishing makes your booking page visible to customers</p>
                                        </div>

                                        {{-- Default View --}}
                                        <div>
                                            <label class="label-text text-sm font-medium mb-2 block">Default Schedule View</label>
                                            <div class="flex gap-3">
                                                <label class="flex items-center gap-2 cursor-pointer p-2 px-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                                    <input type="radio" name="setup_default_view" value="calendar" class="radio radio-primary radio-sm" {{ ($bookingSettings['default_view'] ?? 'calendar') === 'calendar' ? 'checked' : '' }} />
                                                    <span class="icon-[tabler--calendar] size-4"></span>
                                                    <span class="text-sm">Calendar</span>
                                                </label>
                                                <label class="flex items-center gap-2 cursor-pointer p-2 px-3 border border-base-content/10 rounded-lg has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                                                    <input type="radio" name="setup_default_view" value="list" class="radio radio-primary radio-sm" {{ ($bookingSettings['default_view'] ?? 'calendar') === 'list' ? 'checked' : '' }} />
                                                    <span class="icon-[tabler--list] size-4"></span>
                                                    <span class="text-sm">List</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Booking URL Preview --}}
                                        <div class="p-3 bg-base-200/50 rounded-lg">
                                            <label class="text-xs text-base-content/50 mb-1 block">Your Booking Page URL</label>
                                            <div class="flex items-center gap-2">
                                                <span class="icon-[tabler--link] size-4 text-base-content/50"></span>
                                                <code class="text-sm text-primary flex-1 truncate">{{ $bookingUrl }}</code>
                                                <button type="button" onclick="copyBookingUrlSetup()" class="btn btn-ghost btn-xs">
                                                    <span class="icon-[tabler--copy] size-4"></span>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Error/Success Messages --}}
                                        <div id="booking-error" class="alert alert-error alert-sm hidden">
                                            <span class="icon-[tabler--alert-circle] size-4"></span>
                                            <span id="booking-error-text" class="text-sm"></span>
                                        </div>
                                        <div id="booking-success" class="alert alert-success alert-sm hidden">
                                            <span class="icon-[tabler--check] size-4"></span>
                                            <span id="booking-success-text" class="text-sm"></span>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="flex items-center justify-between pt-2">
                                            <a href="{{ route('settings.locations.booking-page') }}" class="btn btn-ghost btn-sm">
                                                <span class="icon-[tabler--settings] size-4"></span>
                                                Advanced Settings
                                            </a>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ $bookingUrl }}" target="_blank" class="btn btn-ghost btn-sm">
                                                    <span class="icon-[tabler--external-link] size-4"></span>
                                                    Preview
                                                </a>
                                                <button type="button" id="save-booking-btn" class="btn btn-primary btn-sm" onclick="saveBookingPageSettings()">
                                                    <span class="icon-[tabler--check] size-4"></span>
                                                    Save & Publish
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CTA when all done --}}
                    @if($progress === 100)
                    <div class="mt-4 p-4 bg-success/10 rounded-lg text-center">
                        <span class="icon-[tabler--confetti] size-6 text-success mb-2"></span>
                        <p class="font-medium text-success">{{ $trans['setup.all_set'] ?? "All set! You're ready to go." }}</p>
                        <form action="{{ route('dashboard.skip-setup') }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                {{ $trans['setup.go_to_dashboard'] ?? 'Go to Dashboard' }}
                                <span class="icon-[tabler--arrow-right] size-4"></span>
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Video & Tips (2 cols) --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Video Card --}}
            <div class="card bg-base-100">
                <div class="card-body p-4">
                    <div class="aspect-video bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg flex items-center justify-center cursor-pointer hover:from-primary/20 hover:to-secondary/20 transition-all group">
                        <div class="text-center">
                            <div class="w-14 h-14 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-2 group-hover:bg-primary/30 transition-all">
                                <span class="icon-[tabler--player-play-filled] size-7 text-primary"></span>
                            </div>
                            <p class="font-medium text-sm">{{ $trans['setup.watch_video'] ?? 'Watch Getting Started' }}</p>
                            <p class="text-xs text-base-content/50">{{ $trans['setup.video_duration'] ?? '2 min video' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Tips --}}
            <div class="card bg-gradient-to-br from-info/5 to-info/10 border border-info/20">
                <div class="card-body p-4">
                    <h3 class="font-semibold text-sm flex items-center gap-2 mb-3">
                        <span class="icon-[tabler--bulb] size-4 text-info"></span>
                        {{ $trans['setup.quick_tips'] ?? 'Quick Tips' }}
                    </h3>
                    <ul class="space-y-2 text-sm text-base-content/70">
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_time'] ?? 'Setup takes about 5 minutes' }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_update'] ?? 'You can update settings anytime' }}</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="icon-[tabler--check] size-4 text-success mt-0.5 flex-shrink-0"></span>
                            <span>{{ $trans['setup.tip_help'] ?? 'Need help?' }} <button type="button" onclick="openSupportModal()" class="link link-primary">{{ $trans['setup.contact_support'] ?? 'Contact support' }}</button></span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Technical Support Card --}}
            <div class="card bg-base-100 border border-base-200">
                <div class="card-body p-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
                            <span class="icon-[tabler--headset] size-5 text-secondary"></span>
                        </div>
                        <div>
                            <h3 class="font-semibold text-sm">{{ $trans['setup.need_help'] ?? 'Need Help?' }}</h3>
                            <p class="text-xs text-base-content/50">{{ $trans['setup.support_desc'] ?? 'Our team is here to assist you' }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="openSupportModal()" class="btn btn-secondary btn-sm w-full">
                        <span class="icon-[tabler--message-circle] size-4"></span>
                        {{ $trans['setup.request_support'] ?? 'Request Technical Support' }}
                    </button>
                    @php
                        $pendingRequests = \App\Models\SupportRequest::forHost($host->id)->whereIn('status', ['pending', 'in_progress'])->count();
                    @endphp
                    @if($pendingRequests > 0)
                    <a href="{{ route('support.requests.index') }}" class="btn btn-ghost btn-xs w-full mt-2">
                        <span class="icon-[tabler--list-check] size-4"></span>
                        View My Requests ({{ $pendingRequests }} active)
                    </a>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
{{-- Phone Verification Modal --}}
<div id="phone-verify-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;" role="dialog" tabindex="-1">
    <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-md mx-4">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <h3 class="text-lg font-semibold">{{ $trans['setup.verify_phone_title'] ?? 'Verify Phone Number' }}</h3>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" aria-label="Close" onclick="closePhoneModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        {{-- Step 1: Enter Phone Number --}}
        <div id="phone-step-1" class="p-4">
                <p class="text-sm text-base-content/70 mb-4">{{ $trans['setup.phone_modal_desc'] ?? 'Enter your phone number to receive a verification code via SMS.' }}</p>

                <form id="phone-form" class="space-y-4">
                    @csrf
                    {{-- Phone Type --}}
                    <div class="form-control">
                        <label class="label" for="phone_type">
                            <span class="label-text font-medium">{{ $trans['setup.phone_type'] ?? 'Number Type' }}</span>
                        </label>
                        <select id="phone_type" name="phone_type" class="select select-bordered w-full" required>
                            <option value="">{{ $trans['setup.select_phone_type'] ?? 'Select number type...' }}</option>
                            <option value="studio">{{ $trans['setup.phone_type_studio'] ?? 'Studio Number' }}</option>
                            <option value="owner">{{ $trans['setup.phone_type_owner'] ?? 'Owner Number' }}</option>
                        </select>
                    </div>

                    {{-- Phone Number --}}
                    <div class="form-control">
                        <label class="label" for="phone_number">
                            <span class="label-text font-medium">{{ $trans['setup.phone_number'] ?? 'Phone Number' }}</span>
                        </label>
                        <div class="input-group">
                            <span class="bg-base-200 px-3 flex items-center">
                                <span class="icon-[tabler--phone] size-4"></span>
                            </span>
                            <input type="tel" id="phone_number" name="phone_number" class="input input-bordered flex-1" placeholder="+1 (555) 000-0000" required>
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">{{ $trans['setup.phone_hint'] ?? 'Include country code (e.g., +1 for US)' }}</span>
                        </label>
                    </div>

                    <div id="phone-error" class="alert alert-error hidden">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span id="phone-error-text"></span>
                    </div>
                </form>
            </div>

        {{-- Step 2: Enter OTP --}}
        <div id="phone-step-2" class="p-4 hidden">
                <div class="text-center mb-4">
                    <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-3">
                        <span class="icon-[tabler--message-code] size-8 text-primary"></span>
                    </div>
                    <p class="text-sm text-base-content/70">{{ $trans['setup.otp_sent'] ?? 'We sent a verification code to' }}</p>
                    <p class="font-semibold" id="display-phone"></p>
                </div>

                <form id="otp-form" class="space-y-4">
                    @csrf
                    <input type="hidden" id="otp_phone" name="phone_number">

                    {{-- OTP Input --}}
                    <div class="form-control">
                        <label class="label" for="otp_code">
                            <span class="label-text font-medium">{{ $trans['setup.enter_code'] ?? 'Enter Verification Code' }}</span>
                        </label>
                        <input type="text" id="otp_code" name="otp_code" class="input input-bordered text-center text-2xl tracking-[0.5em] font-mono" maxlength="6" placeholder="000000" required>
                    </div>

                    <div id="otp-error" class="alert alert-error hidden">
                        <span class="icon-[tabler--alert-circle] size-5"></span>
                        <span id="otp-error-text"></span>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-base-content/50">
                            {{ $trans['setup.didnt_receive'] ?? "Didn't receive the code?" }}
                            <button type="button" id="resend-otp-btn" class="link link-primary" onclick="resendOtp()">{{ $trans['setup.resend_code'] ?? 'Resend' }}</button>
                        </p>
                        <p class="text-xs text-base-content/40 mt-1" id="resend-timer"></p>
                    </div>
                </form>
            </div>

        {{-- Step 3: Success --}}
        <div id="phone-step-3" class="p-4 hidden">
                <div class="text-center py-4">
                    <div class="w-20 h-20 rounded-full bg-success/10 flex items-center justify-center mx-auto mb-4">
                        <span class="icon-[tabler--check] size-10 text-success"></span>
                    </div>
                    <h4 class="text-lg font-semibold text-success mb-2">{{ $trans['setup.phone_verified_success'] ?? 'Phone Verified!' }}</h4>
                    <p class="text-sm text-base-content/70">{{ $trans['setup.phone_verified_desc'] ?? 'Your phone number has been successfully verified.' }}</p>
                </div>
            </div>

        {{-- Modal Footer --}}
        <div class="p-4 border-t border-base-200">
            {{-- Step 1 Footer --}}
            <div id="phone-footer-1" class="flex justify-end gap-2">
                <button type="button" class="btn btn-soft btn-secondary" onclick="closePhoneModal()">{{ $trans['setup.cancel'] ?? 'Cancel' }}</button>
                <button type="button" class="btn btn-primary" id="send-code-btn" onclick="sendVerificationCode()">
                    <span class="icon-[tabler--send] size-4"></span>
                    {{ $trans['setup.send_code'] ?? 'Send Code' }}
                </button>
            </div>

            {{-- Step 2 Footer --}}
            <div id="phone-footer-2" class="hidden flex justify-end gap-2">
                <button type="button" class="btn btn-soft btn-secondary" onclick="goBackToStep1()">{{ $trans['setup.back'] ?? 'Back' }}</button>
                <button type="button" class="btn btn-primary" id="verify-code-btn" onclick="verifyCode()">
                    <span class="icon-[tabler--check] size-4"></span>
                    {{ $trans['setup.verify'] ?? 'Verify' }}
                </button>
            </div>

            {{-- Step 3 Footer --}}
            <div id="phone-footer-3" class="hidden flex justify-end">
                <button type="button" class="btn btn-success" onclick="closeAndRefresh()">
                    <span class="icon-[tabler--check] size-4"></span>
                    {{ $trans['setup.done'] ?? 'Done' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Technical Support Request Modal --}}
<div id="support-request-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;" role="dialog" tabindex="-1">
    <div class="bg-base-100 rounded-xl shadow-xl w-full max-w-lg mx-4">
        {{-- Modal Header --}}
        <div class="flex items-center justify-between p-4 border-b border-base-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-secondary/10 flex items-center justify-center">
                    <span class="icon-[tabler--headset] size-5 text-secondary"></span>
                </div>
                <div>
                    <h3 class="text-lg font-semibold">{{ $trans['support.request_title'] ?? 'Request Technical Support' }}</h3>
                    <p class="text-xs text-base-content/50">{{ $trans['support.request_subtitle'] ?? 'Our team will respond within 24 hours' }}</p>
                </div>
            </div>
            <button type="button" class="btn btn-ghost btn-circle btn-sm" aria-label="Close" onclick="closeSupportModal()">
                <span class="icon-[tabler--x] size-5"></span>
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="support-request-form" class="p-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- First Name --}}
                <div>
                    <label class="label-text text-sm font-medium" for="support_first_name">First Name <span class="text-error">*</span></label>
                    <input
                        type="text"
                        id="support_first_name"
                        name="first_name"
                        class="input input-bordered w-full mt-1"
                        value="{{ auth()->user()->first_name ?? '' }}"
                        placeholder="John"
                        required
                    />
                </div>

                {{-- Last Name --}}
                <div>
                    <label class="label-text text-sm font-medium" for="support_last_name">Last Name <span class="text-error">*</span></label>
                    <input
                        type="text"
                        id="support_last_name"
                        name="last_name"
                        class="input input-bordered w-full mt-1"
                        value="{{ auth()->user()->last_name ?? '' }}"
                        placeholder="Doe"
                        required
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Email Address --}}
                <div>
                    <label class="label-text text-sm font-medium" for="support_email">Email Address <span class="text-error">*</span></label>
                    <input
                        type="email"
                        id="support_email"
                        name="email"
                        class="input input-bordered w-full mt-1"
                        value="{{ auth()->user()->email ?? '' }}"
                        placeholder="john@example.com"
                        required
                    />
                </div>

                {{-- Phone Number --}}
                <div>
                    <label class="label-text text-sm font-medium" for="support_phone">Phone Number</label>
                    <input
                        type="tel"
                        id="support_phone"
                        name="phone"
                        class="input input-bordered w-full mt-1"
                        value="{{ auth()->user()->phone ?? '' }}"
                        placeholder="+1 (555) 123-4567"
                    />
                </div>
            </div>

            {{-- Note / Description --}}
            <div>
                <label class="label-text text-sm font-medium" for="support_note">How can we help? <span class="text-error">*</span></label>
                <textarea
                    id="support_note"
                    name="note"
                    class="textarea textarea-bordered w-full mt-1"
                    rows="4"
                    placeholder="Please describe your issue or question in detail..."
                    required
                ></textarea>
                <p class="text-xs text-base-content/50 mt-1">Please be as detailed as possible so we can assist you better.</p>
            </div>

            {{-- Error/Success Messages --}}
            <div id="support-error" class="alert alert-error hidden">
                <span class="icon-[tabler--alert-circle] size-5"></span>
                <span id="support-error-text"></span>
            </div>
            <div id="support-success" class="alert alert-success hidden">
                <span class="icon-[tabler--check] size-5"></span>
                <span id="support-success-text"></span>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-base-200">
                <button type="button" class="btn btn-ghost" onclick="closeSupportModal()">Cancel</button>
                <button type="submit" id="submit-support-btn" class="btn btn-secondary">
                    <span class="icon-[tabler--send] size-4"></span>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Phone Verification Script --}}
<script>
    // Toast notification function
    function showToast(message, type) {
        type = type || 'success';
        var toast = document.createElement('div');
        toast.className = 'fixed top-4 left-1/2 -translate-x-1/2 z-[100] alert alert-' + type + ' shadow-lg max-w-sm';
        toast.innerHTML = '<span class="icon-[tabler--' + (type === 'success' ? 'check' : 'alert-circle') + '] size-5"></span><span>' + message + '</span>';
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(function() { toast.remove(); }, 300);
        }, 3000);
    }

    // Phone verification modal functionality
    let resendCountdown = null;

    function openPhoneModal() {
        const phoneModal = document.getElementById('phone-verify-modal');
        if (!phoneModal) {
            alert('Modal not found');
            return;
        }
        // Force display flex
        phoneModal.setAttribute('style', 'display: flex !important; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;');
        document.body.style.overflow = 'hidden';
        // Reset to step 1
        showStep(1);
    }

    function closePhoneModal() {
        const phoneModal = document.getElementById('phone-verify-modal');
        if (!phoneModal) return;
        phoneModal.setAttribute('style', 'display: none;');
        document.body.style.overflow = '';
        // Reset form
        const phoneForm = document.getElementById('phone-form');
        const otpForm = document.getElementById('otp-form');
        if (phoneForm) phoneForm.reset();
        if (otpForm) otpForm.reset();
        hideError('phone');
        hideError('otp');
    }

    function showStep(step) {
        // Hide all steps
        document.getElementById('phone-step-1').classList.add('hidden');
        document.getElementById('phone-step-2').classList.add('hidden');
        document.getElementById('phone-step-3').classList.add('hidden');
        document.getElementById('phone-footer-1').classList.add('hidden');
        document.getElementById('phone-footer-2').classList.add('hidden');
        document.getElementById('phone-footer-3').classList.add('hidden');

        // Show requested step
        document.getElementById('phone-step-' + step).classList.remove('hidden');
        document.getElementById('phone-footer-' + step).classList.remove('hidden');
    }

    function goBackToStep1() {
        showStep(1);
    }

    function showError(type, message) {
        const errorDiv = document.getElementById(type + '-error');
        const errorText = document.getElementById(type + '-error-text');
        errorText.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function hideError(type) {
        const errorDiv = document.getElementById(type + '-error');
        errorDiv.classList.add('hidden');
    }

    function setLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        if (loading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> {{ $trans["setup.please_wait"] ?? "Please wait..." }}';
        } else {
            btn.disabled = false;
            if (btnId === 'send-code-btn') {
                btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> {{ $trans["setup.send_code"] ?? "Send Code" }}';
            } else if (btnId === 'verify-code-btn') {
                btn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> {{ $trans["setup.verify"] ?? "Verify" }}';
            }
        }
    }

    async function sendVerificationCode() {
        const phoneType = document.getElementById('phone_type').value;
        const phoneNumber = document.getElementById('phone_number').value;

        // Validation
        if (!phoneType) {
            showError('phone', '{{ $trans["setup.error_select_type"] ?? "Please select a phone number type." }}');
            return;
        }
        if (!phoneNumber) {
            showError('phone', '{{ $trans["setup.error_enter_phone"] ?? "Please enter a phone number." }}');
            return;
        }

        hideError('phone');
        setLoading('send-code-btn', true);

        try {
            const response = await fetch('{{ route("verification.phone.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone_type: phoneType,
                    phone_number: phoneNumber
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Move to step 2
                document.getElementById('display-phone').textContent = phoneNumber;
                document.getElementById('otp_phone').value = phoneNumber;
                showStep(2);
                startResendTimer();
            } else {
                showError('phone', data.message || '{{ $trans["setup.error_send_failed"] ?? "Failed to send verification code. Please try again." }}');
            }
        } catch (error) {
            showError('phone', '{{ $trans["setup.error_network"] ?? "Network error. Please check your connection and try again." }}');
        } finally {
            setLoading('send-code-btn', false);
        }
    }

    async function verifyCode() {
        const phoneNumber = document.getElementById('otp_phone').value;
        const otpCode = document.getElementById('otp_code').value;

        if (!otpCode || otpCode.length < 4) {
            showError('otp', '{{ $trans["setup.error_enter_code"] ?? "Please enter the verification code." }}');
            return;
        }

        hideError('otp');
        setLoading('verify-code-btn', true);

        try {
            const response = await fetch('{{ route("verification.phone.verify") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    otp_code: otpCode
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Move to step 3 (success)
                showStep(3);
            } else {
                showError('otp', data.message || '{{ $trans["setup.error_invalid_code"] ?? "Invalid verification code. Please try again." }}');
            }
        } catch (error) {
            showError('otp', '{{ $trans["setup.error_network"] ?? "Network error. Please check your connection and try again." }}');
        } finally {
            setLoading('verify-code-btn', false);
        }
    }

    function startResendTimer() {
        let seconds = 60;
        const timerEl = document.getElementById('resend-timer');
        const resendBtn = document.getElementById('resend-otp-btn');

        resendBtn.disabled = true;
        resendBtn.classList.add('opacity-50', 'cursor-not-allowed');

        if (resendCountdown) clearInterval(resendCountdown);

        resendCountdown = setInterval(() => {
            seconds--;
            timerEl.textContent = `{{ $trans["setup.resend_in"] ?? "Resend available in" }} ${seconds}s`;

            if (seconds <= 0) {
                clearInterval(resendCountdown);
                timerEl.textContent = '';
                resendBtn.disabled = false;
                resendBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }, 1000);
    }

    async function resendOtp() {
        const phoneNumber = document.getElementById('otp_phone').value;

        hideError('otp');

        try {
            const response = await fetch('{{ route("verification.phone.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    phone_number: phoneNumber,
                    phone_type: document.getElementById('phone_type').value
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                startResendTimer();
            } else {
                showError('otp', data.message || '{{ $trans["setup.error_resend_failed"] ?? "Failed to resend code." }}');
            }
        } catch (error) {
            showError('otp', '{{ $trans["setup.error_network"] ?? "Network error." }}');
        }
    }

    function closeAndRefresh() {
        closePhoneModal();
        window.location.reload();
    }

    // Studio Information Accordion
    let studioInfoExpanded = false;

    function toggleStudioInfoAccordion() {
        const container = document.getElementById('studio-info-form-container');
        const chevron = document.getElementById('studio-info-chevron');

        studioInfoExpanded = !studioInfoExpanded;

        if (studioInfoExpanded) {
            container.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            container.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    // Setup Categories - Search filter
    function filterSetupCategories(query) {
        document.querySelectorAll('.setup-cat-item').forEach(function(item) {
            item.style.display = item.dataset.search.includes(query.toLowerCase()) ? '' : 'none';
        });
    }

    // Toggle setup category
    function toggleSetupCategory(cat) {
        const cb = document.querySelector('.setup-category-checkbox[value="' + cat + '"]');
        if (cb) { cb.checked = !cb.checked; onSetupCategoryChange(); }
    }

    // Update setup category tags
    function onSetupCategoryChange() {
        const tags = document.getElementById('setup-selected-tags');
        let html = '';
        document.querySelectorAll('.setup-category-checkbox:checked').forEach(function(cb) {
            const label = cb.value.length > 20 ? cb.value.substring(0, 17) + '...' : cb.value;
            html += '<span class="badge badge-primary badge-xs gap-1" data-cat="' + cb.value + '">' + label + ' <button type="button" onclick="toggleSetupCategory(\'' + cb.value.replace(/'/g, "\\'") + '\')" class="hover:opacity-70"><span class="icon-[tabler--x] size-3"></span></button></span>';
        });
        tags.innerHTML = html;
        tags.classList.toggle('hidden', html === '');
    }

    // Toggle setup Others section
    function toggleSetupOthers() {
        const section = document.getElementById('setup-custom-section');
        section.classList.toggle('hidden', !document.getElementById('setup-others-checkbox').checked);
    }

    // Toggle cancellation options
    document.addEventListener('DOMContentLoaded', function() {
        const allowCancellations = document.getElementById('setup_allow_cancellations');
        const cancellationOptions = document.getElementById('cancellation-options');

        if (allowCancellations) {
            allowCancellations.addEventListener('change', function() {
                if (this.checked) {
                    cancellationOptions.classList.remove('hidden');
                } else {
                    cancellationOptions.classList.add('hidden');
                }
            });
        }
    });

    // Booking Page Accordion
    let bookingExpanded = false;

    function toggleBookingPageAccordion() {
        const container = document.getElementById('booking-form-container');
        const chevron = document.getElementById('booking-chevron');

        bookingExpanded = !bookingExpanded;

        if (bookingExpanded) {
            container.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            container.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    function copyBookingUrlSetup() {
        const urlElement = document.querySelector('#booking-form-container code');
        if (urlElement) {
            const url = urlElement.textContent.trim();
            navigator.clipboard.writeText(url).then(function() {
                showToast('URL copied to clipboard!', 'success');
            });
        }
    }

    async function saveBookingPageSettings() {
        const btn = document.getElementById('save-booking-btn');
        const errorDiv = document.getElementById('booking-error');
        const errorText = document.getElementById('booking-error-text');
        const successDiv = document.getElementById('booking-success');
        const successText = document.getElementById('booking-success-text');

        // Hide previous messages
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        // Collect form data
        const isPublished = document.getElementById('setup_booking_status').checked;
        const defaultView = document.querySelector('input[name="setup_default_view"]:checked')?.value || 'calendar';

        // Set loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Saving...';

        try {
            const response = await fetch('{{ route("dashboard.save-booking-page") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    booking_page_status: isPublished ? 'published' : 'draft',
                    default_view: defaultView
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successText.textContent = data.message || 'Booking page settings saved!';
                successDiv.classList.remove('hidden');

                // Refresh page after 1.5 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                errorText.textContent = data.message || 'Failed to save. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorText.textContent = 'Network error. Please check your connection.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--check] size-4"></span> Save & Publish';
        }
    }

    // Staff Member Accordion
    let staffExpanded = false;

    function toggleStaffAccordion() {
        const container = document.getElementById('staff-form-container');
        const chevron = document.getElementById('staff-chevron');

        staffExpanded = !staffExpanded;

        if (staffExpanded) {
            container.classList.remove('hidden');
            chevron.classList.add('rotate-180');
        } else {
            container.classList.add('hidden');
            chevron.classList.remove('rotate-180');
        }
    }

    async function sendMemberInvite() {
        const btn = document.getElementById('send-invite-btn');
        const errorDiv = document.getElementById('member-error');
        const errorText = document.getElementById('member-error-text');
        const successDiv = document.getElementById('member-success');
        const successText = document.getElementById('member-success-text');

        // Collect form data
        const memberName = document.getElementById('member_name').value.trim();
        const memberEmail = document.getElementById('member_email').value.trim();
        const memberRole = document.getElementById('member_role').value;

        // Hide messages
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        // Validation
        if (!memberName) {
            errorText.textContent = 'Please enter the member\'s name.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!memberEmail) {
            errorText.textContent = 'Please enter the member\'s email address.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!memberRole) {
            errorText.textContent = 'Please select a role.';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Parse name into first/last
        const nameParts = memberName.split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';

        // Set loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Sending...';

        try {
            const response = await fetch('{{ route("dashboard.quick-invite-member") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: memberEmail,
                    role: memberRole,
                    send_invite: true,
                    quick_invite: true
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Success - show message and refresh after delay
                successText.textContent = data.message || 'Invitation sent successfully!';
                successDiv.classList.remove('hidden');

                // Clear form
                document.getElementById('member_name').value = '';
                document.getElementById('member_email').value = '';
                document.getElementById('member_role').value = '';

                // Refresh page after 2 seconds to update the list
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                errorText.textContent = data.message || 'Failed to send invitation. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorText.textContent = 'Network error. Please check your connection and try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Send an Invite';
        }
    }

    async function saveStudioInfo() {
        const btn = document.getElementById('save-studio-info-btn');
        const errorDiv = document.getElementById('studio-info-error');
        const errorText = document.getElementById('studio-info-error-text');

        // Collect form data
        const studioName = document.getElementById('setup_studio_name').value;
        const studioStructure = document.getElementById('setup_studio_structure').value;
        const subdomain = document.getElementById('setup_subdomain').value;
        const defaultLanguage = document.getElementById('setup_default_language').value;
        const defaultCurrency = document.getElementById('setup_default_currency').value;
        const allowCancellations = document.getElementById('setup_allow_cancellations').checked;
        const cancellationHours = document.getElementById('setup_cancellation_hours').value;

        // Collect selected categories (predefined + custom)
        const selectedCategories = [];
        document.querySelectorAll('.setup-category-checkbox:checked').forEach(function(cb) {
            selectedCategories.push(cb.value);
        });
        // Add custom categories if Others is checked
        if (document.getElementById('setup-others-checkbox').checked) {
            const customText = document.getElementById('setup-custom-categories').value.trim();
            if (customText) {
                customText.split('\n').map(l => l.trim()).filter(l => l).forEach(c => selectedCategories.push(c));
            }
        }

        // Validation
        if (!studioName) {
            errorText.textContent = 'Please enter your studio name.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!studioStructure) {
            errorText.textContent = 'Please select your studio structure.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!subdomain) {
            errorText.textContent = 'Please enter a sub-domain name.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (selectedCategories.length === 0) {
            errorText.textContent = 'Please select at least one studio category.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!defaultLanguage) {
            errorText.textContent = 'Please select a default language.';
            errorDiv.classList.remove('hidden');
            return;
        }
        if (!defaultCurrency) {
            errorText.textContent = 'Please select a default currency.';
            errorDiv.classList.remove('hidden');
            return;
        }

        errorDiv.classList.add('hidden');

        // Set loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-sm"></span> Saving...';

        try {
            const response = await fetch('{{ route("dashboard.save-studio-info") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    studio_name: studioName,
                    studio_structure: studioStructure,
                    subdomain: subdomain,
                    studio_categories: selectedCategories,
                    default_language_app: defaultLanguage,
                    default_currency: defaultCurrency,
                    allow_cancellations: allowCancellations,
                    cancellation_deadline_hours: parseInt(cancellationHours)
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Success - refresh page to update checklist
                window.location.reload();
            } else {
                errorText.textContent = data.message || 'Failed to save. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorText.textContent = 'Network error. Please check your connection and try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--device-floppy] size-4"></span> Save Studio Information';
        }
    }

    // Support Request Modal Functions
    function openSupportModal() {
        const modal = document.getElementById('support-request-modal');
        if (modal) {
            modal.setAttribute('style', 'display: flex !important; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center;');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSupportModal() {
        const modal = document.getElementById('support-request-modal');
        if (modal) {
            modal.setAttribute('style', 'display: none;');
            document.body.style.overflow = '';
            // Reset form
            document.getElementById('support-request-form').reset();
            document.getElementById('support-error').classList.add('hidden');
            document.getElementById('support-success').classList.add('hidden');
        }
    }

    // Support Request Form Submission
    document.getElementById('support-request-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('submit-support-btn');
        const errorDiv = document.getElementById('support-error');
        const errorText = document.getElementById('support-error-text');
        const successDiv = document.getElementById('support-success');
        const successText = document.getElementById('support-success-text');

        // Hide previous messages
        errorDiv.classList.add('hidden');
        successDiv.classList.add('hidden');

        // Get form data
        const firstName = document.getElementById('support_first_name').value.trim();
        const lastName = document.getElementById('support_last_name').value.trim();
        const email = document.getElementById('support_email').value.trim();
        const phone = document.getElementById('support_phone').value.trim();
        const note = document.getElementById('support_note').value.trim();

        // Validate
        if (!firstName || !lastName || !email || !note) {
            errorText.textContent = 'Please fill in all required fields.';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Set loading state
        btn.disabled = true;
        btn.innerHTML = '<span class="loading loading-spinner loading-xs"></span> Submitting...';

        try {
            const response = await fetch('{{ route("support.requests.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    first_name: firstName,
                    last_name: lastName,
                    email: email,
                    phone: phone,
                    note: note
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                successText.textContent = data.message || 'Support request submitted successfully! We will get back to you within 24 hours.';
                successDiv.classList.remove('hidden');

                // Close modal after 2 seconds
                setTimeout(() => {
                    closeSupportModal();
                    window.location.reload();
                }, 2000);
            } else {
                errorText.textContent = data.message || 'Failed to submit request. Please try again.';
                errorDiv.classList.remove('hidden');
            }
        } catch (error) {
            errorText.textContent = 'Network error. Please check your connection and try again.';
            errorDiv.classList.remove('hidden');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<span class="icon-[tabler--send] size-4"></span> Submit Request';
        }
    });
</script>
@endsection
