@php
    $plan = $plan ?? null;
    $features = $plan ? ($plan->features ?? []) : [];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Form --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic Info --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Basic Information</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="name">Plan Name</label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $plan?->name) }}"
                            class="input w-full @error('name') input-error @enderror"
                            placeholder="e.g., Premium"
                            required>
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="slug">Slug</label>
                        <input type="text" id="slug" name="slug"
                            value="{{ old('slug', $plan?->slug) }}"
                            class="input w-full @error('slug') input-error @enderror"
                            placeholder="e.g., premium">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty to auto-generate from name</p>
                        @error('slug')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="label-text" for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                        class="textarea w-full @error('description') input-error @enderror"
                        placeholder="Brief description of this plan...">{{ old('description', $plan?->description) }}</textarea>
                    @error('description')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Pricing</h3>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="price_monthly">Monthly Price ($)</label>
                        <input type="number" id="price_monthly" name="price_monthly"
                            value="{{ old('price_monthly', $plan?->price_monthly ?? 0) }}"
                            class="input w-full @error('price_monthly') input-error @enderror"
                            step="0.01" min="0"
                            required>
                        @error('price_monthly')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="price_yearly">Yearly Price ($)</label>
                        <input type="number" id="price_yearly" name="price_yearly"
                            value="{{ old('price_yearly', $plan?->price_yearly) }}"
                            class="input w-full @error('price_yearly') input-error @enderror"
                            step="0.01" min="0">
                        <p class="text-xs text-base-content/60 mt-1">Leave empty for no yearly option</p>
                        @error('price_yearly')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Paddle Integration --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Paddle Integration</h3>
                <span class="badge badge-soft badge-neutral badge-sm">Optional</span>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label class="label-text" for="paddle_product_id">Paddle Product ID</label>
                    <input type="text" id="paddle_product_id" name="paddle_product_id"
                        value="{{ old('paddle_product_id', $plan?->paddle_product_id) }}"
                        class="input w-full @error('paddle_product_id') input-error @enderror"
                        placeholder="pro_xxxxxxxxxx">
                    @error('paddle_product_id')
                        <p class="text-error text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label-text" for="paddle_monthly_price_id">Monthly Price ID</label>
                        <input type="text" id="paddle_monthly_price_id" name="paddle_monthly_price_id"
                            value="{{ old('paddle_monthly_price_id', $plan?->paddle_monthly_price_id) }}"
                            class="input w-full @error('paddle_monthly_price_id') input-error @enderror"
                            placeholder="pri_xxxxxxxxxx">
                        @error('paddle_monthly_price_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="label-text" for="paddle_yearly_price_id">Yearly Price ID</label>
                        <input type="text" id="paddle_yearly_price_id" name="paddle_yearly_price_id"
                            value="{{ old('paddle_yearly_price_id', $plan?->paddle_yearly_price_id) }}"
                            class="input w-full @error('paddle_yearly_price_id') input-error @enderror"
                            placeholder="pri_xxxxxxxxxx">
                        @error('paddle_yearly_price_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Features & Limits</h3>
            </div>
            <div class="card-body space-y-6">
                {{-- Limits --}}
                <div>
                    <h4 class="font-medium mb-3">Resource Limits</h4>
                    <p class="text-sm text-base-content/60 mb-4">Set to 0 for unlimited</p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="label-text" for="feature_locations">Locations</label>
                            <input type="number" id="feature_locations" name="feature_locations"
                                value="{{ old('feature_locations', $features['locations'] ?? 1) }}"
                                class="input w-full" min="0">
                        </div>
                        <div>
                            <label class="label-text" for="feature_rooms">Rooms</label>
                            <input type="number" id="feature_rooms" name="feature_rooms"
                                value="{{ old('feature_rooms', $features['rooms'] ?? 3) }}"
                                class="input w-full" min="0">
                        </div>
                        <div>
                            <label class="label-text" for="feature_classes">Classes</label>
                            <input type="number" id="feature_classes" name="feature_classes"
                                value="{{ old('feature_classes', $features['classes'] ?? 10) }}"
                                class="input w-full" min="0">
                        </div>
                        <div>
                            <label class="label-text" for="feature_students">Students</label>
                            <input type="number" id="feature_students" name="feature_students"
                                value="{{ old('feature_students', $features['students'] ?? 100) }}"
                                class="input w-full" min="0">
                        </div>
                    </div>
                </div>

                {{-- Feature Toggles --}}
                <div>
                    <h4 class="font-medium mb-3">Feature Access</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @php
                            $featureToggles = [
                                'crm' => ['label' => 'CRM Features', 'icon' => 'users'],
                                'manual_payments' => ['label' => 'Manual Payments', 'icon' => 'cash'],
                                'online_payments' => ['label' => 'Online Payments', 'icon' => 'credit-card'],
                                'stripe_payments' => ['label' => 'Stripe Payments', 'icon' => 'brand-stripe'],
                                'memberships' => ['label' => 'Memberships', 'icon' => 'id-badge'],
                                'intro_offers' => ['label' => 'Intro Offers', 'icon' => 'gift'],
                                'automated_emails' => ['label' => 'Automated Emails', 'icon' => 'mail-forward'],
                                'attendance_insights' => ['label' => 'Attendance Insights', 'icon' => 'chart-bar'],
                                'revenue_insights' => ['label' => 'Revenue Insights', 'icon' => 'chart-pie'],
                                'ics_sync' => ['label' => 'iCal/ICS Sync', 'icon' => 'calendar-event'],
                                'fitnearyou_attribution' => ['label' => 'FitNearYou Attribution', 'icon' => 'map-pin'],
                                'priority_support' => ['label' => 'Priority Support', 'icon' => 'headset'],
                            ];
                        @endphp

                        @foreach($featureToggles as $key => $info)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-base-content/10 cursor-pointer hover:bg-base-200/50">
                            <input type="checkbox" name="feature_{{ $key }}" value="1"
                                class="checkbox checkbox-sm checkbox-primary"
                                {{ old("feature_{$key}", $features[$key] ?? false) ? 'checked' : '' }}>
                            <span class="icon-[tabler--{{ $info['icon'] }}] size-5 text-base-content/40"></span>
                            <span class="text-sm">{{ $info['label'] }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        {{-- Status --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Status</h3>
            </div>
            <div class="card-body space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1"
                        class="toggle toggle-primary"
                        {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Active</span>
                        <p class="text-xs text-base-content/60">Plan is available for selection</p>
                    </div>
                </label>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1"
                        class="toggle toggle-secondary"
                        {{ old('is_featured', $plan?->is_featured ?? false) ? 'checked' : '' }}>
                    <div>
                        <span class="font-medium">Featured</span>
                        <p class="text-xs text-base-content/60">Highlight this plan</p>
                    </div>
                </label>

                <div>
                    <label class="label-text" for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order"
                        value="{{ old('sort_order', $plan?->sort_order ?? 0) }}"
                        class="input w-full" min="0">
                    <p class="text-xs text-base-content/60 mt-1">Lower numbers appear first</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card bg-base-100">
            <div class="card-body">
                <button type="submit" class="btn btn-primary w-full">
                    <span class="icon-[tabler--check] size-5"></span>
                    {{ $plan ? 'Update Plan' : 'Create Plan' }}
                </button>
                <a href="{{ route('backoffice.plans.index') }}" class="btn btn-ghost w-full">
                    Cancel
                </a>
            </div>
        </div>

        @if($plan)
        {{-- Usage Stats --}}
        <div class="card bg-base-100">
            <div class="card-header">
                <h3 class="card-title">Usage</h3>
            </div>
            <div class="card-body">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Clients using this plan</dt>
                        <dd class="font-medium">{{ $plan->hosts()->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Created</dt>
                        <dd>{{ $plan->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-base-content/60">Last Updated</dt>
                        <dd>{{ $plan->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        @endif
    </div>
</div>
