@extends('layouts.settings')

@section('title', 'Tax Settings — Settings')

@section('breadcrumbs')
    <ol>
        <li><a href="{{ url('/dashboard') }}"><span class="icon-[tabler--home] size-4"></span> Dashboard</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li><a href="{{ route('settings.index') }}"><span class="icon-[tabler--settings] me-1 size-4"></span> Settings</a></li>
        <li class="breadcrumbs-separator rtl:rotate-180"><span class="icon-[tabler--chevron-right]"></span></li>
        <li aria-current="page">Tax Settings</li>
    </ol>
@endsection

@php
$taxIdLabels = [
    'Tax ID' => 'Tax ID',
    'EIN' => 'EIN (US)',
    'VAT ID' => 'VAT ID (EU)',
    'GST/HST Number' => 'GST/HST Number (CA)',
    'GSTIN' => 'GSTIN (India)',
    'ABN' => 'ABN (Australia)',
    'RFC' => 'RFC (Mexico)',
    'TIN' => 'TIN (Generic)',
];

$serviceTypes = [
    'class' => 'Classes',
    'service' => 'Services',
    'membership' => 'Memberships',
    'pack' => 'Class Packs',
];
@endphp

@section('settings-content')
<div class="space-y-6">

    {{-- No Operating Countries Warning --}}
    @if(empty($operatingCountries))
    <div class="alert alert-warning">
        <span class="icon-[tabler--alert-triangle] size-5"></span>
        <div>
            <p class="font-medium">No operating countries configured</p>
            <p class="text-sm">Please <a href="{{ route('settings.studio.profile') }}" class="link link-primary">set your operating countries</a> in Studio Profile to manage tax rates.</p>
        </div>
    </div>
    @endif

    {{-- Tax Configuration Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-6">Tax Collection</h2>

            <div class="space-y-6">
                {{-- Enable/Disable Toggle --}}
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Enable Tax Collection</div>
                        <div class="text-sm text-base-content/60">Automatically calculate and apply taxes to transactions</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" id="tax_enabled" {{ ($taxSettings['tax_enabled'] ?? false) ? 'checked' : '' }} onchange="updateTaxSettings()" />
                </div>

                <div class="divider my-0"></div>

                {{-- Tax Calculation Method --}}
                <div>
                    <label class="label-text font-medium">Price Display</label>
                    <p class="text-sm text-base-content/60 mb-3">How prices are displayed to customers</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="tax_calculation_method" value="exclusive" class="radio radio-primary radio-sm" {{ ($taxSettings['tax_calculation_method'] ?? 'exclusive') === 'exclusive' ? 'checked' : '' }} onchange="updateTaxSettings()" />
                            <div>
                                <span class="text-sm font-medium">Tax Exclusive</span>
                                <span class="text-xs text-base-content/60 block">Show prices before tax, add tax at checkout</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="tax_calculation_method" value="inclusive" class="radio radio-primary radio-sm" {{ ($taxSettings['tax_calculation_method'] ?? 'exclusive') === 'inclusive' ? 'checked' : '' }} onchange="updateTaxSettings()" />
                            <div>
                                <span class="text-sm font-medium">Tax Inclusive</span>
                                <span class="text-xs text-base-content/60 block">Tax is already included in displayed prices</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="divider my-0"></div>

                {{-- Tax Display Mode --}}
                <div>
                    <label class="label-text font-medium">Receipt Display</label>
                    <p class="text-sm text-base-content/60 mb-3">How tax is shown on invoices and receipts</p>
                    <div class="space-y-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="tax_display_mode" value="combined" class="radio radio-primary radio-sm" {{ ($taxSettings['tax_display_mode'] ?? 'combined') === 'combined' ? 'checked' : '' }} onchange="updateTaxSettings()" />
                            <div>
                                <span class="text-sm font-medium">Combined</span>
                                <span class="text-xs text-base-content/60 block">Show single "Tax" line</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio" name="tax_display_mode" value="itemized" class="radio radio-primary radio-sm" {{ ($taxSettings['tax_display_mode'] ?? 'combined') === 'itemized' ? 'checked' : '' }} onchange="updateTaxSettings()" />
                            <div>
                                <span class="text-sm font-medium">Itemized</span>
                                <span class="text-xs text-base-content/60 block">Show breakdown (e.g., GST + PST, CGST + SGST)</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="divider my-0"></div>

                {{-- Show on Receipts --}}
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Show Tax on Receipts</div>
                        <div class="text-sm text-base-content/60">Display tax breakdown on customer receipts and invoices</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" id="show_tax_on_receipts" {{ ($taxSettings['show_tax_on_receipts'] ?? true) ? 'checked' : '' }} onchange="updateTaxSettings()" />
                </div>
            </div>
        </div>
    </div>

    {{-- Tax Identification Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Tax Identification</h2>
            <p class="text-sm text-base-content/60 mb-6">Your business tax registration number shown on invoices</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="label-text" for="tax_id_label">Tax ID Type</label>
                    <select id="tax_id_label" class="select w-full" onchange="updateTaxSettings()">
                        @foreach($taxIdLabels as $value => $label)
                            <option value="{{ $value }}" {{ ($taxSettings['tax_id_label'] ?? 'Tax ID') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label-text" for="tax_id">Tax ID Number</label>
                    <input type="text" id="tax_id" class="input w-full" value="{{ $taxSettings['tax_id'] ?? '' }}" placeholder="Enter your tax ID" onblur="updateTaxSettings()" />
                </div>
            </div>
        </div>
    </div>

    {{-- Tax Exemptions Card --}}
    <div class="card bg-base-100">
        <div class="card-body">
            <h2 class="text-lg font-semibold mb-2">Tax Exemptions</h2>
            <p class="text-sm text-base-content/60 mb-6">Configure when tax should not be applied</p>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Default Tax Exempt</div>
                        <div class="text-sm text-base-content/60">Make all new services/plans tax exempt by default</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary" id="default_tax_exempt" {{ ($taxSettings['default_tax_exempt'] ?? false) ? 'checked' : '' }} onchange="updateTaxSettings()" />
                </div>

                <div class="divider my-0"></div>

                <div>
                    <div class="font-medium mb-2">Skip Tax for Credit Payments</div>
                    <p class="text-sm text-base-content/60 mb-3">Don't charge tax when customers pay with:</p>
                    <div class="flex flex-wrap gap-3">
                        @php $exemptMethods = $taxSettings['exempt_payment_methods'] ?? []; @endphp
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" id="exempt_membership" {{ in_array('membership', $exemptMethods) ? 'checked' : '' }} onchange="updateExemptMethods()" />
                            <span class="text-sm">Membership Credits</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" id="exempt_pack" {{ in_array('pack', $exemptMethods) ? 'checked' : '' }} onchange="updateExemptMethods()" />
                            <span class="text-sm">Class Pack Credits</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tax Rates Card --}}
    @if(!empty($operatingCountries))
    <div class="card bg-base-100">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Tax Rates</h2>
                    <p class="text-base-content/60 text-sm">Manage tax rates for your operating countries</p>
                </div>
                <button type="button" class="btn btn-primary btn-sm" onclick="openDrawer('add-rate-drawer')">
                    <span class="icon-[tabler--plus] size-4"></span> Add Custom Rate
                </button>
            </div>

            {{-- Country Tabs --}}
            @if(count($operatingCountries) > 1)
            <div class="tabs tabs-bordered mb-4" role="tablist">
                @foreach($operatingCountries as $index => $countryCode)
                <button type="button" class="tab {{ $index === 0 ? 'tab-active' : '' }}" role="tab" data-country="{{ $countryCode }}" onclick="switchCountryTab('{{ $countryCode }}')">
                    {{ $countryNames[$countryCode] ?? $countryCode }}
                    <span class="badge badge-sm badge-ghost ms-1">{{ isset($ratesByCountry[$countryCode]) ? $ratesByCountry[$countryCode]->count() : 0 }}</span>
                </button>
                @endforeach
            </div>
            @endif

            {{-- Rates Tables by Country --}}
            @foreach($operatingCountries as $index => $countryCode)
            <div class="country-rates-table {{ $index === 0 ? '' : 'hidden' }}" id="rates-{{ $countryCode }}">
                @if(count($operatingCountries) === 1)
                <h3 class="font-medium text-sm text-base-content/60 mb-3">{{ $countryNames[$countryCode] ?? $countryCode }}</h3>
                @endif

                @if(isset($ratesByCountry[$countryCode]) && $ratesByCountry[$countryCode]->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Region</th>
                                <th>Tax Name</th>
                                <th>Type</th>
                                <th class="text-right">Rate</th>
                                <th class="text-center">Status</th>
                                <th class="w-24"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ratesByCountry[$countryCode]->sortBy(['state_code', 'priority']) as $rate)
                            <tr id="rate-row-{{ $rate->id }}" data-rate-id="{{ $rate->id }}">
                                <td>
                                    <span class="font-medium">{{ $rate->state_code ?: 'Country-wide' }}</span>
                                    @if($rate->city)
                                    <span class="text-xs text-base-content/60 block">{{ $rate->city }}</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $rate->tax_name }}
                                    @if($rate->host_id)
                                    <span class="badge badge-xs badge-info ms-1">Custom</span>
                                    @endif
                                    @if($rate->has_override ?? false)
                                    <span class="badge badge-xs badge-warning ms-1">Override: {{ $rate->override_rate }}%</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-soft badge-sm">{{ $taxTypeLabels[$rate->tax_type] ?? $rate->tax_type }}</span>
                                </td>
                                <td class="text-right font-mono">
                                    @if($rate->has_override ?? false)
                                    <span class="line-through text-base-content/40">{{ number_format($rate->rate, 3) }}%</span>
                                    <span class="text-primary font-medium ms-1">{{ number_format($rate->override_rate, 3) }}%</span>
                                    @else
                                    {{ number_format($rate->rate, 3) }}%
                                    @endif
                                </td>
                                <td class="text-center">
                                    <input type="checkbox" class="toggle toggle-sm toggle-success" {{ ($rate->is_enabled ?? $rate->is_active) ? 'checked' : '' }} onchange="toggleRate({{ $rate->override_id ?? $rate->id }}, this.checked, {{ $rate->host_id ? 'true' : 'false' }})" />
                                </td>
                                <td>
                                    <div class="flex items-center justify-end gap-1">
                                        @if(!$rate->host_id && !($rate->has_override ?? false))
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="openOverrideDrawer({{ json_encode($rate) }})" data-tooltip="Override Rate">
                                            <span class="icon-[tabler--edit] size-4"></span>
                                        </button>
                                        @elseif($rate->host_id || ($rate->has_override ?? false))
                                        <button type="button" class="btn btn-ghost btn-xs" onclick="openEditRateDrawer({{ json_encode($rate) }})" data-tooltip="Edit">
                                            <span class="icon-[tabler--pencil] size-4"></span>
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs text-error" onclick="deleteRate({{ $rate->override_id ?? $rate->id }})" data-tooltip="Delete">
                                            <span class="icon-[tabler--trash] size-4"></span>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <span class="icon-[tabler--receipt-tax] size-12 text-base-content/20 mx-auto block"></span>
                    <p class="text-base-content/50 mt-2">No tax rates configured for {{ $countryNames[$countryCode] ?? $countryCode }}</p>
                    <button type="button" class="btn btn-primary btn-sm mt-4" onclick="openDrawer('add-rate-drawer'); document.getElementById('rate_country_code').value = '{{ $countryCode }}'">
                        <span class="icon-[tabler--plus] size-4"></span> Add Tax Rate
                    </button>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

{{-- Drawer Backdrop --}}
<div id="drawer-backdrop" class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300" onclick="closeAllDrawers()"></div>

{{-- Add/Edit Tax Rate Drawer --}}
<div id="add-rate-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold" id="rate-drawer-title">Add Custom Tax Rate</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('add-rate-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="rate-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="saveRate(event)">
        <input type="hidden" id="rate_id" value="" />
        <input type="hidden" id="rate_mode" value="create" />
        <div class="flex-1 overflow-y-auto p-4">
            <div class="space-y-4">
                <div>
                    <label class="label-text" for="rate_country_code">Country <span class="text-error">*</span></label>
                    <select id="rate_country_code" class="select w-full" required onchange="updateStateOptions()">
                        <option value="">Select country...</option>
                        @foreach($operatingCountries as $code)
                        <option value="{{ $code }}">{{ $countryNames[$code] ?? $code }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="state-field">
                    <label class="label-text" for="rate_state_code">State/Province</label>
                    <select id="rate_state_code" class="select w-full">
                        <option value="">Country-wide (all states)</option>
                    </select>
                </div>

                <div>
                    <label class="label-text" for="rate_city">City (Optional)</label>
                    <input type="text" id="rate_city" class="input w-full" placeholder="Leave blank for state/country-wide" />
                </div>

                <div class="divider my-2"></div>

                <div>
                    <label class="label-text" for="rate_tax_name">Tax Name <span class="text-error">*</span></label>
                    <input type="text" id="rate_tax_name" class="input w-full" placeholder="e.g., California Sales Tax" required />
                </div>

                <div>
                    <label class="label-text" for="rate_tax_type">Tax Type <span class="text-error">*</span></label>
                    <select id="rate_tax_type" class="select w-full" required>
                        @foreach($taxTypeLabels as $type => $label)
                        <option value="{{ $type }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label-text" for="rate_rate">Tax Rate (%) <span class="text-error">*</span></label>
                    <input type="number" id="rate_rate" class="input w-full" step="0.001" min="0" max="100" placeholder="e.g., 8.25" required />
                </div>

                <div class="divider my-2"></div>

                <div>
                    <label class="label-text">Applies To</label>
                    <p class="text-xs text-base-content/60 mb-2">Leave all unchecked to apply to all service types</p>
                    <div class="flex flex-wrap gap-3">
                        @foreach($serviceTypes as $type => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-sm" name="applies_to[]" value="{{ $type }}" />
                            <span class="text-sm">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium text-sm">Active</div>
                        <div class="text-xs text-base-content/60">Enable this tax rate</div>
                    </div>
                    <input type="checkbox" class="toggle toggle-primary toggle-sm" id="rate_is_active" checked />
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-rate-btn">
                <span class="loading loading-spinner loading-xs hidden" id="rate-spinner"></span>
                Save Rate
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('add-rate-drawer')">Cancel</button>
        </div>
    </form>
</div>

{{-- Override Rate Drawer --}}
<div id="override-rate-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-base-100 shadow-xl z-50 transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
    <div class="flex items-center justify-between p-4 border-b border-base-200">
        <h3 class="text-lg font-semibold">Override Tax Rate</h3>
        <button type="button" class="btn btn-ghost btn-circle btn-sm" onclick="closeDrawer('override-rate-drawer')">
            <span class="icon-[tabler--x] size-5"></span>
        </button>
    </div>
    <form id="override-form" class="flex flex-col flex-1 overflow-hidden" onsubmit="saveOverride(event)">
        <input type="hidden" id="override_system_rate_id" value="" />
        <div class="flex-1 overflow-y-auto p-4">
            <div class="alert alert-info mb-4">
                <span class="icon-[tabler--info-circle] size-5"></span>
                <span class="text-sm">You're creating a custom override for a system default rate. The original rate will be preserved.</span>
            </div>

            <div class="space-y-4">
                <div class="bg-base-200/50 rounded-lg p-4">
                    <div class="text-sm text-base-content/60 mb-1">Original Rate</div>
                    <div class="font-medium" id="override-original-name">-</div>
                    <div class="text-sm" id="override-original-rate">-</div>
                </div>

                <div>
                    <label class="label-text" for="override_rate">New Rate (%) <span class="text-error">*</span></label>
                    <input type="number" id="override_rate" class="input w-full" step="0.001" min="0" max="100" required />
                    <p class="text-xs text-base-content/60 mt-1">Enter your custom tax rate percentage</p>
                </div>
            </div>
        </div>
        <div class="flex justify-start gap-2 p-4 border-t border-base-200 bg-base-100">
            <button type="submit" class="btn btn-primary" id="save-override-btn">
                <span class="loading loading-spinner loading-xs hidden" id="override-spinner"></span>
                Save Override
            </button>
            <button type="button" class="btn btn-soft btn-secondary" onclick="closeDrawer('override-rate-drawer')">Cancel</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// State/Province data by country
var statesByCountry = {
    'US': [
        { code: 'AL', name: 'Alabama' }, { code: 'AK', name: 'Alaska' }, { code: 'AZ', name: 'Arizona' },
        { code: 'AR', name: 'Arkansas' }, { code: 'CA', name: 'California' }, { code: 'CO', name: 'Colorado' },
        { code: 'CT', name: 'Connecticut' }, { code: 'DE', name: 'Delaware' }, { code: 'FL', name: 'Florida' },
        { code: 'GA', name: 'Georgia' }, { code: 'HI', name: 'Hawaii' }, { code: 'ID', name: 'Idaho' },
        { code: 'IL', name: 'Illinois' }, { code: 'IN', name: 'Indiana' }, { code: 'IA', name: 'Iowa' },
        { code: 'KS', name: 'Kansas' }, { code: 'KY', name: 'Kentucky' }, { code: 'LA', name: 'Louisiana' },
        { code: 'ME', name: 'Maine' }, { code: 'MD', name: 'Maryland' }, { code: 'MA', name: 'Massachusetts' },
        { code: 'MI', name: 'Michigan' }, { code: 'MN', name: 'Minnesota' }, { code: 'MS', name: 'Mississippi' },
        { code: 'MO', name: 'Missouri' }, { code: 'MT', name: 'Montana' }, { code: 'NE', name: 'Nebraska' },
        { code: 'NV', name: 'Nevada' }, { code: 'NH', name: 'New Hampshire' }, { code: 'NJ', name: 'New Jersey' },
        { code: 'NM', name: 'New Mexico' }, { code: 'NY', name: 'New York' }, { code: 'NC', name: 'North Carolina' },
        { code: 'ND', name: 'North Dakota' }, { code: 'OH', name: 'Ohio' }, { code: 'OK', name: 'Oklahoma' },
        { code: 'OR', name: 'Oregon' }, { code: 'PA', name: 'Pennsylvania' }, { code: 'RI', name: 'Rhode Island' },
        { code: 'SC', name: 'South Carolina' }, { code: 'SD', name: 'South Dakota' }, { code: 'TN', name: 'Tennessee' },
        { code: 'TX', name: 'Texas' }, { code: 'UT', name: 'Utah' }, { code: 'VT', name: 'Vermont' },
        { code: 'VA', name: 'Virginia' }, { code: 'WA', name: 'Washington' }, { code: 'WV', name: 'West Virginia' },
        { code: 'WI', name: 'Wisconsin' }, { code: 'WY', name: 'Wyoming' }, { code: 'DC', name: 'Washington D.C.' }
    ],
    'CA': [
        { code: 'AB', name: 'Alberta' }, { code: 'BC', name: 'British Columbia' }, { code: 'MB', name: 'Manitoba' },
        { code: 'NB', name: 'New Brunswick' }, { code: 'NL', name: 'Newfoundland and Labrador' },
        { code: 'NS', name: 'Nova Scotia' }, { code: 'NT', name: 'Northwest Territories' },
        { code: 'NU', name: 'Nunavut' }, { code: 'ON', name: 'Ontario' }, { code: 'PE', name: 'Prince Edward Island' },
        { code: 'QC', name: 'Quebec' }, { code: 'SK', name: 'Saskatchewan' }, { code: 'YT', name: 'Yukon' }
    ],
    'IN': [
        { code: 'AN', name: 'Andaman and Nicobar' }, { code: 'AP', name: 'Andhra Pradesh' },
        { code: 'AR', name: 'Arunachal Pradesh' }, { code: 'AS', name: 'Assam' }, { code: 'BR', name: 'Bihar' },
        { code: 'CH', name: 'Chandigarh' }, { code: 'CT', name: 'Chhattisgarh' }, { code: 'DL', name: 'Delhi' },
        { code: 'GA', name: 'Goa' }, { code: 'GJ', name: 'Gujarat' }, { code: 'HR', name: 'Haryana' },
        { code: 'HP', name: 'Himachal Pradesh' }, { code: 'JK', name: 'Jammu and Kashmir' },
        { code: 'JH', name: 'Jharkhand' }, { code: 'KA', name: 'Karnataka' }, { code: 'KL', name: 'Kerala' },
        { code: 'MP', name: 'Madhya Pradesh' }, { code: 'MH', name: 'Maharashtra' }, { code: 'MN', name: 'Manipur' },
        { code: 'ML', name: 'Meghalaya' }, { code: 'MZ', name: 'Mizoram' }, { code: 'NL', name: 'Nagaland' },
        { code: 'OR', name: 'Odisha' }, { code: 'PB', name: 'Punjab' }, { code: 'RJ', name: 'Rajasthan' },
        { code: 'SK', name: 'Sikkim' }, { code: 'TN', name: 'Tamil Nadu' }, { code: 'TG', name: 'Telangana' },
        { code: 'TR', name: 'Tripura' }, { code: 'UP', name: 'Uttar Pradesh' }, { code: 'UK', name: 'Uttarakhand' },
        { code: 'WB', name: 'West Bengal' }
    ],
    'AU': [
        { code: 'NSW', name: 'New South Wales' }, { code: 'VIC', name: 'Victoria' },
        { code: 'QLD', name: 'Queensland' }, { code: 'WA', name: 'Western Australia' },
        { code: 'SA', name: 'South Australia' }, { code: 'TAS', name: 'Tasmania' },
        { code: 'ACT', name: 'Australian Capital Territory' }, { code: 'NT', name: 'Northern Territory' }
    ],
    'MX': [
        { code: 'AGU', name: 'Aguascalientes' }, { code: 'BCN', name: 'Baja California' },
        { code: 'BCS', name: 'Baja California Sur' }, { code: 'CAM', name: 'Campeche' },
        { code: 'CHP', name: 'Chiapas' }, { code: 'CHH', name: 'Chihuahua' }, { code: 'COA', name: 'Coahuila' },
        { code: 'COL', name: 'Colima' }, { code: 'CMX', name: 'Mexico City' }, { code: 'DUR', name: 'Durango' },
        { code: 'GUA', name: 'Guanajuato' }, { code: 'GRO', name: 'Guerrero' }, { code: 'HID', name: 'Hidalgo' },
        { code: 'JAL', name: 'Jalisco' }, { code: 'MEX', name: 'State of Mexico' }, { code: 'MIC', name: 'Michoacan' },
        { code: 'MOR', name: 'Morelos' }, { code: 'NAY', name: 'Nayarit' }, { code: 'NLE', name: 'Nuevo Leon' },
        { code: 'OAX', name: 'Oaxaca' }, { code: 'PUE', name: 'Puebla' }, { code: 'QUE', name: 'Queretaro' },
        { code: 'ROO', name: 'Quintana Roo' }, { code: 'SLP', name: 'San Luis Potosi' }, { code: 'SIN', name: 'Sinaloa' },
        { code: 'SON', name: 'Sonora' }, { code: 'TAB', name: 'Tabasco' }, { code: 'TAM', name: 'Tamaulipas' },
        { code: 'TLA', name: 'Tlaxcala' }, { code: 'VER', name: 'Veracruz' }, { code: 'YUC', name: 'Yucatan' },
        { code: 'ZAC', name: 'Zacatecas' }
    ],
    'DE': [], // Germany - no states needed for tax purposes
    'FR': [], // France - no states needed for tax purposes
    'GB': [] // UK - no states needed for tax purposes
};

// Drawer functions
function openDrawer(drawerId) {
    var drawer = document.getElementById(drawerId);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer && backdrop) {
        drawer.classList.remove('translate-x-full');
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
    }
}

function closeDrawer(drawerId) {
    var drawer = document.getElementById(drawerId);
    var backdrop = document.getElementById('drawer-backdrop');
    if (drawer && backdrop) {
        drawer.classList.add('translate-x-full');
        backdrop.classList.add('opacity-0', 'pointer-events-none');
    }
}

function closeAllDrawers() {
    document.querySelectorAll('[id$="-drawer"]').forEach(function(drawer) {
        if (!drawer.id.includes('backdrop')) {
            drawer.classList.add('translate-x-full');
        }
    });
    var backdrop = document.getElementById('drawer-backdrop');
    if (backdrop) {
        backdrop.classList.add('opacity-0', 'pointer-events-none');
    }
}

// Toast notification
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

// Country tab switching
function switchCountryTab(countryCode) {
    // Update tab active states
    document.querySelectorAll('.tabs .tab').forEach(function(tab) {
        if (tab.dataset.country === countryCode) {
            tab.classList.add('tab-active');
        } else {
            tab.classList.remove('tab-active');
        }
    });

    // Show/hide rate tables
    document.querySelectorAll('.country-rates-table').forEach(function(table) {
        if (table.id === 'rates-' + countryCode) {
            table.classList.remove('hidden');
        } else {
            table.classList.add('hidden');
        }
    });
}

// Update state options based on country
function updateStateOptions() {
    var countrySelect = document.getElementById('rate_country_code');
    var stateSelect = document.getElementById('rate_state_code');
    var country = countrySelect.value;

    stateSelect.innerHTML = '<option value="">Country-wide (all states)</option>';

    if (country && statesByCountry[country] && statesByCountry[country].length > 0) {
        statesByCountry[country].forEach(function(state) {
            var option = document.createElement('option');
            option.value = state.code;
            option.textContent = state.name + ' (' + state.code + ')';
            stateSelect.appendChild(option);
        });
    }
}

// Update tax settings
function updateTaxSettings() {
    var exemptMethods = [];
    if (document.getElementById('exempt_membership').checked) exemptMethods.push('membership');
    if (document.getElementById('exempt_pack').checked) exemptMethods.push('pack');

    var data = {
        tax_enabled: document.getElementById('tax_enabled').checked,
        tax_calculation_method: document.querySelector('input[name="tax_calculation_method"]:checked').value,
        tax_display_mode: document.querySelector('input[name="tax_display_mode"]:checked').value,
        show_tax_on_receipts: document.getElementById('show_tax_on_receipts').checked,
        tax_id: document.getElementById('tax_id').value,
        tax_id_label: document.getElementById('tax_id_label').value,
        default_tax_exempt: document.getElementById('default_tax_exempt').checked,
        exempt_payment_methods: exemptMethods
    };

    fetch('{{ route("settings.payments.tax.update") }}', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Settings saved');
        } else {
            showToast(result.message || 'Failed to save', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    });
}

function updateExemptMethods() {
    updateTaxSettings();
}

// Toggle rate active status
function toggleRate(rateId, isActive, isCustom) {
    if (!isCustom) {
        // For system rates, we need to create an override to disable
        showToast('Create an override to disable system rates', 'warning');
        return;
    }

    fetch('{{ url("/settings/payments/tax/rates") }}/' + rateId + '/toggle', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ is_active: isActive })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast(isActive ? 'Rate enabled' : 'Rate disabled');
        } else {
            showToast(result.message || 'Failed to update', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    });
}

// Open override drawer
function openOverrideDrawer(rate) {
    document.getElementById('override_system_rate_id').value = rate.id;
    document.getElementById('override-original-name').textContent = rate.tax_name + ' (' + (rate.state_code || 'Country-wide') + ')';
    document.getElementById('override-original-rate').textContent = rate.rate + '%';
    document.getElementById('override_rate').value = rate.rate;
    openDrawer('override-rate-drawer');
}

// Save override
function saveOverride(event) {
    event.preventDefault();

    var btn = document.getElementById('save-override-btn');
    var spinner = document.getElementById('override-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var systemRateId = document.getElementById('override_system_rate_id').value;
    var newRate = document.getElementById('override_rate').value;

    fetch('{{ url("/settings/payments/tax/rates") }}/' + systemRateId + '/override', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ rate: newRate })
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Rate override created');
            closeDrawer('override-rate-drawer');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(result.message || 'Failed to create override', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

// Open edit drawer for custom rate
function openEditRateDrawer(rate) {
    document.getElementById('rate-drawer-title').textContent = 'Edit Tax Rate';
    document.getElementById('rate_id').value = rate.override_id || rate.id;
    document.getElementById('rate_mode').value = 'edit';
    document.getElementById('rate_country_code').value = rate.country_code;
    updateStateOptions();
    document.getElementById('rate_state_code').value = rate.state_code || '';
    document.getElementById('rate_city').value = rate.city || '';
    document.getElementById('rate_tax_name').value = rate.tax_name;
    document.getElementById('rate_tax_type').value = rate.tax_type;
    document.getElementById('rate_rate').value = rate.override_rate || rate.rate;
    document.getElementById('rate_is_active').checked = rate.is_enabled !== undefined ? rate.is_enabled : rate.is_active;

    // Set applies_to checkboxes
    var appliesTo = rate.applies_to || [];
    document.querySelectorAll('input[name="applies_to[]"]').forEach(function(cb) {
        cb.checked = appliesTo.length === 0 || appliesTo.includes(cb.value);
    });

    openDrawer('add-rate-drawer');
}

// Save rate (create or update)
function saveRate(event) {
    event.preventDefault();

    var btn = document.getElementById('save-rate-btn');
    var spinner = document.getElementById('rate-spinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');

    var mode = document.getElementById('rate_mode').value;
    var rateId = document.getElementById('rate_id').value;

    var appliesTo = [];
    document.querySelectorAll('input[name="applies_to[]"]:checked').forEach(function(cb) {
        appliesTo.push(cb.value);
    });

    var data = {
        country_code: document.getElementById('rate_country_code').value,
        state_code: document.getElementById('rate_state_code').value || null,
        city: document.getElementById('rate_city').value || null,
        tax_name: document.getElementById('rate_tax_name').value,
        tax_type: document.getElementById('rate_tax_type').value,
        rate: document.getElementById('rate_rate').value,
        is_active: document.getElementById('rate_is_active').checked,
        applies_to: appliesTo.length > 0 && appliesTo.length < 4 ? appliesTo : null
    };

    var url = mode === 'edit'
        ? '{{ url("/settings/payments/tax/rates") }}/' + rateId
        : '{{ route("settings.payments.tax.rates.store") }}';

    var method = mode === 'edit' ? 'PUT' : 'POST';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast(mode === 'edit' ? 'Rate updated' : 'Rate created');
            closeDrawer('add-rate-drawer');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(result.message || 'Failed to save', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        spinner.classList.add('hidden');
    });
}

// Delete rate
function deleteRate(rateId) {
    if (!confirm('Are you sure you want to delete this tax rate?')) return;

    fetch('{{ url("/settings/payments/tax/rates") }}/' + rateId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(result) {
        if (result.success) {
            showToast('Rate deleted');
            var row = document.getElementById('rate-row-' + rateId);
            if (row) row.remove();
        } else {
            showToast(result.message || 'Failed to delete', 'error');
        }
    })
    .catch(function() {
        showToast('An error occurred', 'error');
    });
}

// Reset form when opening add drawer
document.addEventListener('DOMContentLoaded', function() {
    var addRateBtn = document.querySelector('[onclick*="add-rate-drawer"]');
    if (addRateBtn) {
        var origOnclick = addRateBtn.onclick;
        addRateBtn.onclick = function() {
            document.getElementById('rate-drawer-title').textContent = 'Add Custom Tax Rate';
            document.getElementById('rate_id').value = '';
            document.getElementById('rate_mode').value = 'create';
            document.getElementById('rate-form').reset();
            document.getElementById('rate_is_active').checked = true;
            if (origOnclick) origOnclick.call(this);
        };
    }
});
</script>
@endpush
