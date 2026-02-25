<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    // Redirect /settings to first accessible settings page based on permissions
    public function index()
    {
        $user = auth()->user();

        // Check permissions in priority order and redirect to first accessible page
        if ($user->hasPermission('studio.profile')) {
            return redirect()->route('settings.studio.profile');
        }

        if ($user->hasPermission('studio.locations')) {
            return redirect()->route('settings.locations.index');
        }

        if ($user->hasPermission('team.view') || $user->hasPermission('team.manage')) {
            return redirect()->route('settings.team.users');
        }

        if ($user->hasPermission('team.instructors')) {
            return redirect()->route('settings.team.instructors');
        }

        if ($user->hasPermission('team.permissions')) {
            return redirect()->route('settings.team.permissions');
        }

        if ($user->hasPermission('payments.stripe')) {
            return redirect()->route('settings.payments.settings');
        }

        if ($user->hasPermission('billing.plan')) {
            return redirect()->route('settings.billing.plan');
        }

        if ($user->hasPermission('billing.invoices')) {
            return redirect()->route('settings.billing.invoices');
        }

        // Everyone can access their own profile
        return redirect()->route('settings.profile');
    }

    // ─────────────────────────────────────────────────────────────
    // My Profile (accessible to all authenticated users)
    // ─────────────────────────────────────────────────────────────

    public function myProfile()
    {
        $user = auth()->user();
        $host = $user->currentHost() ?? $user->host;

        // Get instructor profile if user is linked to one
        $instructor = null;
        if ($user->instructor_id) {
            $instructor = $user->instructor;
        }

        // Get the user's role and permissions for current host
        $membership = $user->hosts()->where('hosts.id', $host->id)->first();
        $role = $membership?->pivot?->role ?? $user->role;
        $permissions = $membership?->pivot?->permissions ?? $user->permissions;

        return view('host.settings.profile.index', compact('user', 'host', 'instructor', 'role', 'permissions'));
    }

    public function updateMyProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255|regex:/^[^\d]*$/',
            'last_name' => 'required|string|max:255|regex:/^[^\d]*$/',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
        ], [
            'first_name.regex' => 'First name cannot contain numbers.',
            'last_name.regex' => 'Last name cannot contain numbers.',
        ]);

        $user->update($validated);

        // If user has linked instructor profile, update that too
        if ($user->instructor_id && $user->instructor) {
            $user->instructor->update([
                'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->instructor->phone,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => $user->fresh(),
        ]);
    }

    public function updateMyPassword(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user->update([
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    public function uploadMyPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = auth()->user();
        $disk = config('filesystems.uploads');

        // Delete old photo if exists (try-catch for cloud storage compatibility)
        if ($user->profile_photo) {
            try {
                \Storage::disk($disk)->delete($user->profile_photo);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        // Store the file with public visibility
        $host = auth()->user()->currentHost();
        $path = $request->file('photo')->storePublicly($host->getStoragePath('profile-photos'), $disk);

        $user->update(['profile_photo' => $path]);

        // Also update instructor photo if linked
        if ($user->instructor_id && $user->instructor) {
            if ($user->instructor->photo_path) {
                try {
                    \Storage::disk($disk)->delete($user->instructor->photo_path);
                } catch (\Exception $e) {
                    // Ignore deletion errors
                }
            }
            $user->instructor->update(['photo_path' => $path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo uploaded successfully.',
            'photo_url' => \Storage::disk($disk)->url($path),
        ]);
    }

    public function removeMyPhoto()
    {
        $user = auth()->user();
        $disk = config('filesystems.uploads');

        if ($user->profile_photo && \Storage::disk($disk)->exists($user->profile_photo)) {
            \Storage::disk($disk)->delete($user->profile_photo);
        }

        $user->update(['profile_photo' => null]);

        // Also remove instructor photo if linked
        if ($user->instructor_id && $user->instructor) {
            $user->instructor->update(['profile_photo' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo removed successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Studio
    // ─────────────────────────────────────────────────────────────

    public function studioProfile()
    {
        $host = auth()->user()->host;
        $defaultLocation = $host->defaultLocation();
        return view('host.settings.studio.profile', compact('host', 'defaultLocation'));
    }

    public function updateStudioProfile(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'studio_name' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:200',
            'city' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'timezone' => 'required|string|max:100',
            'studio_types' => 'nullable|array',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Studio profile updated successfully',
            'data' => $host->fresh(),
        ]);
    }

    public function updateStudioAbout(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'about' => 'nullable|string|max:2000',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Studio description updated successfully',
        ]);
    }

    public function uploadStudioLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Delete old logo if exists (try-catch for cloud storage compatibility)
        if ($host->logo_path) {
            try {
                \Storage::disk(config('filesystems.uploads'))->delete($host->logo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        // Store new logo
        $path = $request->file('logo')->storePublicly($host->getStoragePath('branding'), config('filesystems.uploads'));
        $host->update(['logo_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Logo uploaded successfully',
            'logo_url' => \Storage::disk(config('filesystems.uploads'))->url($path),
        ]);
    }

    public function uploadStudioCover(Request $request)
    {
        $request->validate([
            'cover' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Delete old cover if exists (try-catch for cloud storage compatibility)
        if ($host->cover_image_path) {
            try {
                \Storage::disk(config('filesystems.uploads'))->delete($host->cover_image_path);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        // Store new cover
        $path = $request->file('cover')->storePublicly($host->getStoragePath('branding'), config('filesystems.uploads'));
        $host->update(['cover_image_path' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Cover image uploaded successfully',
            'cover_url' => \Storage::disk(config('filesystems.uploads'))->url($path),
        ]);
    }

    public function updateStudioContact(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'studio_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'contact_name' => 'nullable|string|max:255',
            'support_email' => 'nullable|email|max:255',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Contact information updated successfully',
        ]);
    }

    public function updateStudioSocial(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'social_links' => 'nullable|array',
            'social_links.instagram' => 'nullable|url|max:255',
            'social_links.facebook' => 'nullable|url|max:255',
            'social_links.website' => 'nullable|url|max:255',
            'social_links.tiktok' => 'nullable|url|max:255',
        ]);

        $host->update(['social_links' => $validated['social_links'] ?? []]);

        return response()->json([
            'success' => true,
            'message' => 'Social links updated successfully',
        ]);
    }

    public function updateStudioAmenities(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'amenities' => 'nullable|array',
        ]);

        $host->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Amenities updated successfully',
        ]);
    }

    public function updateStudioCurrency(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'currencies' => 'nullable|array',
            'currencies.*' => 'string|size:3',
            'default_currency' => 'nullable|string|size:3',
        ]);

        $currencies = $validated['currencies'] ?? [];
        $defaultCurrency = $validated['default_currency'] ?? 'USD';

        // Ensure default currency is in the currencies array
        if (!in_array($defaultCurrency, $currencies)) {
            $currencies[] = $defaultCurrency;
        }

        $host->update([
            'currencies' => $currencies,
            'default_currency' => $defaultCurrency,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Currencies updated successfully',
        ]);
    }

    public function updateStudioCountries(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'operating_countries' => 'nullable|array',
            'operating_countries.*' => 'string|size:2|in:US,CA,DE,GB,AU,IN',
        ]);

        $host->update([
            'operating_countries' => $validated['operating_countries'] ?? [],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Countries of operation updated successfully',
        ]);
    }

    public function updateStudioLanguage(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'studio_languages' => 'nullable|array',
            'studio_languages.*' => 'string|in:en,fr,de,es',
            'default_language_app' => 'required|string|in:en,fr,de,es',
            'default_language_booking' => 'required|string|in:en,fr,de,es',
        ]);

        // Ensure at least English is selected
        $studioLanguages = $validated['studio_languages'] ?? ['en'];
        if (empty($studioLanguages)) {
            $studioLanguages = ['en'];
        }

        $host->update([
            'studio_languages' => $studioLanguages,
            'default_language_app' => $validated['default_language_app'],
            'default_language_booking' => $validated['default_language_booking'],
        ]);

        // Clear translation cache when language settings change
        app(\App\Services\TranslationService::class)->clearCache($host->id);

        return response()->json([
            'success' => true,
            'message' => 'Language settings updated successfully',
            'data' => [
                'studio_languages' => $studioLanguages,
                'default_language_app' => $validated['default_language_app'],
                'default_language_booking' => $validated['default_language_booking'],
            ],
        ]);
    }

    public function updateStudioCancellation(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'allow_cancellations' => 'boolean',
            'cancellation_window_hours' => 'required|integer|in:0,2,6,12,24,48,72',
        ]);

        // Get existing policies and merge with new values
        $policies = $host->policies ?? [];
        $policies['allow_cancellations'] = $validated['allow_cancellations'] ?? true;
        $policies['cancellation_window_hours'] = $validated['cancellation_window_hours'];

        $host->update(['policies' => $policies]);

        return response()->json([
            'success' => true,
            'message' => 'Cancellation policy updated successfully',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Locations
    // ─────────────────────────────────────────────────────────────

    public function rooms()
    {
        return view('host.settings.locations.rooms');
    }

    public function bookingPage()
    {
        return view('host.settings.locations.booking-page');
    }

    public function policies()
    {
        return view('host.settings.locations.policies');
    }

    // ─────────────────────────────────────────────────────────────
    // Team
    // ─────────────────────────────────────────────────────────────

    public function users()
    {
        return view('host.settings.team.users');
    }

    public function instructors()
    {
        return view('host.settings.team.instructors');
    }

    public function permissions()
    {
        return view('host.settings.team.permissions');
    }

    // ─────────────────────────────────────────────────────────────
    // Clients
    // ─────────────────────────────────────────────────────────────

    public function clientSettings()
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        // Get current client settings or defaults
        $settings = $host->client_settings ?? [
            'default_status' => 'lead',
            'auto_archive_days' => null,
            'require_phone' => false,
            'require_address' => false,
            'enable_member_portal' => false,
            'member_portal_features' => [],
            'at_risk_days' => 30,
            'enable_lead_scoring' => false,
        ];

        return view('host.settings.clients.index', compact('host', 'settings'));
    }

    public function updateClientSettings(\Illuminate\Http\Request $request)
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        $validated = $request->validate([
            'default_status' => 'required|in:lead,client',
            'auto_archive_days' => 'nullable|integer|min:30|max:365',
            'require_phone' => 'boolean',
            'require_address' => 'boolean',
            'enable_member_portal' => 'boolean',
            'member_portal_features' => 'nullable|array',
            'at_risk_days' => 'required|integer|min:7|max:90',
            'enable_lead_scoring' => 'boolean',
        ]);

        $host->update(['client_settings' => $validated]);

        return response()->json([
            'success' => true,
            'message' => 'Client settings updated successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Member Portal
    // ─────────────────────────────────────────────────────────────

    public function memberPortal()
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        // Get current member portal settings or defaults
        $settings = $host->member_portal_settings ?? $this->getDefaultMemberPortalSettings();

        return view('host.settings.member-portal.index', compact('host', 'settings'));
    }

    public function updateMemberPortal(Request $request)
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;

        $validated = $request->validate([
            'enabled' => 'boolean',
            'login_method' => 'required|in:otp,password',
            'session_timeout_days' => 'required|integer|min:1|max:90',
            'require_email_verification' => 'boolean',
            'activation_code_expiry_minutes' => 'required|integer|min:5|max:60',
            'max_otp_resend_per_hour' => 'required|integer|min:1|max:10',
            'max_login_attempts' => 'required|integer|min:3|max:20',
            'lockout_duration_minutes' => 'required|integer|min:5|max:120',
            'allowed_features' => 'nullable|array',
            'allowed_features.*' => 'string|in:schedule,bookings,payments,invoices,profile,intake_forms',
        ]);

        // Ensure boolean fields are properly cast
        $validated['enabled'] = $request->boolean('enabled');
        $validated['require_email_verification'] = $request->boolean('require_email_verification');

        $host->update(['member_portal_settings' => $validated]);

        return response()->json([
            'success' => true,
            'message' => 'Member portal settings updated successfully.',
        ]);
    }

    protected function getDefaultMemberPortalSettings(): array
    {
        return [
            'enabled' => false,
            'login_method' => 'otp',
            'session_timeout_days' => 30,
            'require_email_verification' => false,
            'activation_code_expiry_minutes' => 10,
            'max_otp_resend_per_hour' => 3,
            'max_login_attempts' => 10,
            'lockout_duration_minutes' => 30,
            'allowed_features' => ['schedule', 'bookings', 'payments', 'invoices', 'profile'],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Payments
    // ─────────────────────────────────────────────────────────────

    public function paymentSettings()
    {
        $host = auth()->user()->host;
        return view('host.settings.payments.settings', compact('host'));
    }

    public function updatePaymentSettings(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'accept_cards' => 'boolean',
            'accept_cash' => 'boolean',
            'currency' => 'nullable|string|size:3',
            'send_receipts' => 'boolean',
            'receipt_footer' => 'nullable|string|max:500',
            'manual_methods' => 'nullable|array',
            'manual_methods.*.enabled' => 'boolean',
            'manual_methods.*.instructions' => 'nullable|string|max:500',
        ]);

        // Ensure boolean fields are properly cast
        $validated['accept_cards'] = $request->boolean('accept_cards');
        $validated['accept_cash'] = $request->boolean('accept_cash');
        $validated['send_receipts'] = $request->boolean('send_receipts');

        // Process manual methods to ensure proper boolean casting
        if (isset($validated['manual_methods'])) {
            foreach ($validated['manual_methods'] as $key => $method) {
                $validated['manual_methods'][$key]['enabled'] = filter_var($method['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            }
        }

        $host->update(['payment_settings' => $validated]);

        return response()->json([
            'success' => true,
            'message' => 'Payment settings updated successfully',
        ]);
    }

    public function taxSettings(TaxService $taxService)
    {
        $host = auth()->user()->host;
        $taxSettings = $host->tax_settings ?? TaxService::getDefaultTaxSettings();

        // Get operating countries or fall back to host's country
        $operatingCountries = $host->operating_countries ?? [];
        if (empty($operatingCountries) && $host->country) {
            $operatingCountries = [$host->country];
        }

        // Get tax rates for operating countries
        $taxRates = collect();
        if (!empty($operatingCountries)) {
            $taxRates = $taxService->getSystemRatesForCountries($operatingCountries);

            // Merge with any host-specific custom rates
            $customRates = TaxRate::where('host_id', $host->id)
                ->whereIn('country_code', $operatingCountries)
                ->get();

            // Mark system rates that have been overridden
            $taxRates = $taxRates->map(function ($rate) use ($customRates) {
                $override = $customRates->first(function ($custom) use ($rate) {
                    return $custom->country_code === $rate->country_code
                        && $custom->state_code === $rate->state_code
                        && $custom->tax_type === $rate->tax_type;
                });

                $rate->has_override = $override !== null;
                $rate->override_rate = $override ? $override->rate : null;
                $rate->override_id = $override ? $override->id : null;
                $rate->is_enabled = $override ? $override->is_active : $rate->is_active;

                return $rate;
            });

            // Add any custom rates that don't have a system equivalent
            $customOnlyRates = $customRates->filter(function ($custom) use ($taxRates) {
                return !$taxRates->contains(function ($rate) use ($custom) {
                    return $rate->country_code === $custom->country_code
                        && $rate->state_code === $custom->state_code
                        && $rate->tax_type === $custom->tax_type;
                });
            });

            $taxRates = $taxRates->concat($customOnlyRates);
        }

        // Group rates by country
        $ratesByCountry = $taxRates->groupBy('country_code');

        return view('host.settings.payments.tax', [
            'host' => $host,
            'taxSettings' => $taxSettings,
            'ratesByCountry' => $ratesByCountry,
            'operatingCountries' => $operatingCountries,
            'countryNames' => TaxRate::getCountryNames(),
            'taxTypeLabels' => TaxRate::getTaxTypeLabels(),
        ]);
    }

    public function updateTaxSettings(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'tax_enabled' => 'boolean',
            'tax_calculation_method' => 'nullable|in:exclusive,inclusive',
            'tax_display_mode' => 'nullable|in:combined,itemized',
            'tax_id' => 'nullable|string|max:50',
            'tax_id_label' => 'nullable|string|max:30',
            'show_tax_on_receipts' => 'boolean',
            'default_tax_exempt' => 'boolean',
            'exempt_payment_methods' => 'nullable|array',
            'exempt_payment_methods.*' => 'string|in:membership,pack',
            'round_tax' => 'nullable|in:standard,up,down',
        ]);

        // Merge with existing settings
        $existingSettings = $host->tax_settings ?? TaxService::getDefaultTaxSettings();
        $newSettings = array_merge($existingSettings, [
            'tax_enabled' => $request->boolean('tax_enabled'),
            'tax_calculation_method' => $validated['tax_calculation_method'] ?? 'exclusive',
            'tax_display_mode' => $validated['tax_display_mode'] ?? 'combined',
            'tax_id' => $validated['tax_id'] ?? null,
            'tax_id_label' => $validated['tax_id_label'] ?? 'Tax ID',
            'show_tax_on_receipts' => $request->boolean('show_tax_on_receipts'),
            'default_tax_exempt' => $request->boolean('default_tax_exempt'),
            'exempt_payment_methods' => $validated['exempt_payment_methods'] ?? [],
            'round_tax' => $validated['round_tax'] ?? 'standard',
        ]);

        $host->update(['tax_settings' => $newSettings]);

        return response()->json([
            'success' => true,
            'message' => 'Tax settings updated successfully',
        ]);
    }

    public function storeTaxRate(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'country_code' => 'required|string|size:2',
            'state_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:100',
            'tax_name' => 'required|string|max:100',
            'tax_type' => 'required|string|max:50',
            'rate' => 'required|numeric|min:0|max:100',
            'applies_to' => 'nullable|array',
            'applies_to.*' => 'string|in:class,service,membership,pack',
        ]);

        $taxRate = TaxRate::create([
            'host_id' => $host->id,
            'country_code' => $validated['country_code'],
            'state_code' => $validated['state_code'] ?? null,
            'city' => $validated['city'] ?? null,
            'tax_name' => $validated['tax_name'],
            'tax_type' => $validated['tax_type'],
            'rate' => $validated['rate'],
            'applies_to' => $validated['applies_to'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate created successfully',
            'rate' => $taxRate,
        ]);
    }

    public function updateTaxRate(Request $request, $id)
    {
        $host = auth()->user()->host;

        $taxRate = TaxRate::where('host_id', $host->id)->findOrFail($id);

        $validated = $request->validate([
            'tax_name' => 'sometimes|required|string|max:100',
            'rate' => 'sometimes|required|numeric|min:0|max:100',
            'applies_to' => 'nullable|array',
            'applies_to.*' => 'string|in:class,service,membership,pack',
            'is_active' => 'sometimes|boolean',
        ]);

        $taxRate->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tax rate updated successfully',
            'rate' => $taxRate,
        ]);
    }

    public function deleteTaxRate($id)
    {
        $host = auth()->user()->host;

        $taxRate = TaxRate::where('host_id', $host->id)->findOrFail($id);
        $taxRate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax rate deleted successfully',
        ]);
    }

    public function toggleTaxRate(Request $request, $id)
    {
        $host = auth()->user()->host;

        // Check if this is a system rate or custom rate
        $systemRate = TaxRate::whereNull('host_id')->find($id);

        if ($systemRate) {
            // Create or update a host-specific override
            $override = TaxRate::where('host_id', $host->id)
                ->where('country_code', $systemRate->country_code)
                ->where('state_code', $systemRate->state_code)
                ->where('tax_type', $systemRate->tax_type)
                ->first();

            if ($override) {
                $override->update(['is_active' => !$override->is_active]);
            } else {
                // Create a new override with toggled status
                TaxRate::create([
                    'host_id' => $host->id,
                    'country_code' => $systemRate->country_code,
                    'state_code' => $systemRate->state_code,
                    'city' => $systemRate->city,
                    'tax_name' => $systemRate->tax_name,
                    'tax_type' => $systemRate->tax_type,
                    'rate' => $systemRate->rate,
                    'is_compound' => $systemRate->is_compound,
                    'priority' => $systemRate->priority,
                    'applies_to' => $systemRate->applies_to,
                    'is_active' => false, // Toggle from system default (true) to false
                ]);
            }
        } else {
            // Toggle custom rate
            $customRate = TaxRate::where('host_id', $host->id)->findOrFail($id);
            $customRate->update(['is_active' => !$customRate->is_active]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tax rate status updated',
        ]);
    }

    public function overrideTaxRate(Request $request, $id)
    {
        $host = auth()->user()->host;

        $systemRate = TaxRate::whereNull('host_id')->findOrFail($id);

        $validated = $request->validate([
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        // Check for existing override
        $override = TaxRate::where('host_id', $host->id)
            ->where('country_code', $systemRate->country_code)
            ->where('state_code', $systemRate->state_code)
            ->where('tax_type', $systemRate->tax_type)
            ->first();

        if ($override) {
            $override->update(['rate' => $validated['rate']]);
        } else {
            $override = TaxRate::create([
                'host_id' => $host->id,
                'country_code' => $systemRate->country_code,
                'state_code' => $systemRate->state_code,
                'city' => $systemRate->city,
                'tax_name' => $systemRate->tax_name,
                'tax_type' => $systemRate->tax_type,
                'rate' => $validated['rate'],
                'is_compound' => $systemRate->is_compound,
                'priority' => $systemRate->priority,
                'applies_to' => $systemRate->applies_to,
                'is_active' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tax rate overridden successfully',
            'rate' => $override,
        ]);
    }

    public function payoutPreferences()
    {
        return view('host.settings.payments.payouts');
    }

    // ─────────────────────────────────────────────────────────────
    // Notifications
    // ─────────────────────────────────────────────────────────────

    public function emailNotifications()
    {
        return view('host.settings.notifications.email');
    }

    public function smsNotifications()
    {
        return view('host.settings.notifications.sms');
    }

    public function automationRules()
    {
        return view('host.settings.notifications.automation');
    }

    // ─────────────────────────────────────────────────────────────
    // Integrations
    // ─────────────────────────────────────────────────────────────

    public function stripeIntegration()
    {
        return view('host.settings.integrations.stripe');
    }

    public function fitNearYouIntegration()
    {
        return view('host.settings.integrations.fitnearyou');
    }

    public function calendarSync()
    {
        return view('host.settings.integrations.calendar');
    }

    public function paypalIntegration()
    {
        return view('host.settings.integrations.paypal');
    }

    public function cashAppIntegration()
    {
        return view('host.settings.integrations.cashapp');
    }

    public function venmoIntegration()
    {
        return view('host.settings.integrations.venmo');
    }

    // ─────────────────────────────────────────────────────────────
    // Plans & Billing
    // ─────────────────────────────────────────────────────────────

    public function currentPlan()
    {
        return view('host.settings.billing.plan');
    }

    public function usage()
    {
        return view('host.settings.billing.usage');
    }

    public function invoices()
    {
        return view('host.settings.billing.invoices');
    }

    // ─────────────────────────────────────────────────────────────
    // Advanced
    // ─────────────────────────────────────────────────────────────

    public function dataExport()
    {
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

        return view('host.settings.advanced.export');
    }

    public function auditLogs()
    {
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

        return view('host.settings.advanced.audit');
    }

    public function dangerZone()
    {
        // Owner only
        if (!auth()->user()->isOwner()) {
            return redirect()->route('settings.index')
                ->with('error', 'Only the studio owner can access this page.');
        }

        return view('host.settings.advanced.danger');
    }

    // ─────────────────────────────────────────────────────────────
    // Developer Tools (only in local/development)
    // ─────────────────────────────────────────────────────────────

    public function emailLogs()
    {
        // Only allow in local environment
        if (!app()->environment('local')) {
            abort(404);
        }

        $emails = [];
        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            $content = file_get_contents($logPath);

            // Match email log entries - Laravel logs emails with "local.DEBUG: From:"
            // Pattern: [timestamp] local.DEBUG: From: ... followed by email content until next log entry
            preg_match_all('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\][^\[]*?local\.DEBUG: From:([^\n]+)\nTo:([^\n]+)\nSubject:([^\n]+)(.*?)(?=\[\d{4}-\d{2}-\d{2}|\z)/s', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $timestamp = $match[1];
                $from = trim($match[2]);
                $to = trim($match[3]);
                $subject = trim($match[4]);
                $body = trim($match[5]);

                // Extract HTML content from multipart email
                // Look for Content-Type: text/html section
                $html = '';
                if (preg_match('/Content-Type:\s*text\/html[^\n]*\n(?:Content-Transfer-Encoding:[^\n]*\n)?\n(<!DOCTYPE.*?<\/html>)/si', $body, $htmlMatch)) {
                    $html = $htmlMatch[1];
                } elseif (preg_match('/(<!DOCTYPE.*?<\/html>)/si', $body, $htmlMatch)) {
                    // Fallback: just find the HTML document
                    $html = $htmlMatch[1];
                }

                // Extract plain text for preview
                $plainBody = '';
                if (preg_match('/Content-Type:\s*text\/plain[^\n]*\n(?:Content-Transfer-Encoding:[^\n]*\n)?\n(.*?)(?=--[a-zA-Z0-9_]+|$)/si', $body, $textMatch)) {
                    $plainBody = trim($textMatch[1]);
                    $plainBody = preg_replace('/\s+/', ' ', $plainBody);
                    $plainBody = substr($plainBody, 0, 200) . (strlen($plainBody) > 200 ? '...' : '');
                }

                $emails[] = [
                    'timestamp' => $timestamp,
                    'to' => $to,
                    'from' => $from,
                    'subject' => $subject,
                    'body' => $plainBody,
                    'html' => $html,
                ];
            }

            // Reverse to show newest first
            $emails = array_reverse($emails);
        }

        return view('host.settings.advanced.email-logs', compact('emails'));
    }

    public function clearEmailLogs()
    {
        // Only allow in local environment
        if (!app()->environment('local')) {
            abort(404);
        }

        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return back()->with('success', 'Email logs cleared.');
    }

    /**
     * Upload gallery images (supports multiple files).
     */
    public function uploadGalleryImage(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Get max sort order
        $maxSort = \App\Models\StudioGalleryImage::where('host_id', $host->id)->max('sort_order') ?? -1;

        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            $maxSort++;

            // Store the image
            $path = $file->storePublicly($host->getStoragePath('gallery'), config('filesystems.uploads'));

            // Create the gallery image record
            $galleryImage = \App\Models\StudioGalleryImage::create([
                'host_id' => $host->id,
                'image_path' => $path,
                'caption' => null,
                'sort_order' => $maxSort,
            ]);

            $uploadedImages[] = [
                'id' => $galleryImage->id,
                'image_url' => $galleryImage->image_url,
                'caption' => $galleryImage->caption,
                'sort_order' => $galleryImage->sort_order,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedImages) . ' image(s) uploaded successfully',
            'images' => $uploadedImages,
        ]);
    }

    /**
     * Update gallery image caption.
     */
    public function updateGalleryImage(Request $request, $id)
    {
        $host = auth()->user()->host;

        $galleryImage = \App\Models\StudioGalleryImage::where('host_id', $host->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'caption' => 'nullable|string|max:255',
        ]);

        $galleryImage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
        ]);
    }

    /**
     * Delete a gallery image.
     */
    public function deleteGalleryImage($id)
    {
        $host = auth()->user()->host;

        $galleryImage = \App\Models\StudioGalleryImage::where('host_id', $host->id)
            ->findOrFail($id);

        // Delete the file
        try {
            \Storage::disk(config('filesystems.uploads'))->delete($galleryImage->image_path);
        } catch (\Exception $e) {
            // Ignore deletion errors
        }

        $galleryImage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Reorder gallery images.
     */
    public function reorderGalleryImages(Request $request)
    {
        $host = auth()->user()->host;

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:studio_gallery_images,id',
        ]);

        foreach ($request->order as $index => $id) {
            \App\Models\StudioGalleryImage::where('host_id', $host->id)
                ->where('id', $id)
                ->update(['sort_order' => $index]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gallery order updated',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Studio Certifications
    // ─────────────────────────────────────────────────────────────

    /**
     * Store a new certification.
     */
    public function storeCertification(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'certification_name' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
        ]);

        $certification = new \App\Models\StudioCertification([
            'host_id' => $host->id,
            'name' => $validated['name'],
            'certification_name' => $validated['certification_name'] ?? null,
            'expire_date' => $validated['expire_date'] ?? null,
            'reminder_days' => $validated['reminder_days'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storePublicly($host->getStoragePath('certifications'), config('filesystems.uploads'));
            $certification->file_path = $path;
            $certification->file_name = $file->getClientOriginalName();
        }

        $certification->save();

        return response()->json([
            'success' => true,
            'message' => 'Certification added successfully',
            'certification' => [
                'id' => $certification->id,
                'name' => $certification->name,
                'certification_name' => $certification->certification_name,
                'expire_date' => $certification->expire_date?->format('Y-m-d'),
                'expire_date_formatted' => $certification->expire_date?->format('M j, Y'),
                'reminder_days' => $certification->reminder_days,
                'notes' => $certification->notes,
                'file_url' => $certification->file_url,
                'file_name' => $certification->file_name,
                'status_label' => $certification->status_label,
                'status_badge_class' => $certification->status_badge_class,
                'is_expired' => $certification->isExpired(),
            ],
        ]);
    }

    /**
     * Get a single certification for editing.
     */
    public function getCertification($id)
    {
        $host = auth()->user()->host;

        $certification = \App\Models\StudioCertification::where('host_id', $host->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'certification' => [
                'id' => $certification->id,
                'name' => $certification->name,
                'certification_name' => $certification->certification_name,
                'expire_date' => $certification->expire_date?->format('Y-m-d'),
                'reminder_days' => $certification->reminder_days,
                'notes' => $certification->notes,
                'file_url' => $certification->file_url,
                'file_name' => $certification->file_name,
            ],
        ]);
    }

    /**
     * Update a certification.
     */
    public function updateCertification(Request $request, $id)
    {
        $host = auth()->user()->host;

        $certification = \App\Models\StudioCertification::where('host_id', $host->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'certification_name' => 'nullable|string|max:255',
            'expire_date' => 'nullable|date',
            'reminder_days' => 'nullable|integer|min:1|max:365',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240',
            'remove_file' => 'nullable|boolean',
        ]);

        $certification->name = $validated['name'];
        $certification->certification_name = $validated['certification_name'] ?? null;
        $certification->expire_date = $validated['expire_date'] ?? null;
        $certification->reminder_days = $validated['reminder_days'] ?? null;
        $certification->notes = $validated['notes'] ?? null;

        // Reset reminder if expiry date changed
        if ($certification->isDirty('expire_date')) {
            $certification->reminder_sent = false;
        }

        // Handle file removal
        if ($request->boolean('remove_file') && $certification->file_path) {
            try {
                \Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            } catch (\Exception $e) {
                // Ignore deletion errors
            }
            $certification->file_path = null;
            $certification->file_name = null;
        }

        // Handle file upload
        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($certification->file_path) {
                try {
                    \Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
                } catch (\Exception $e) {
                    // Ignore deletion errors
                }
            }

            $file = $request->file('file');
            $path = $file->storePublicly($host->getStoragePath('certifications'), config('filesystems.uploads'));
            $certification->file_path = $path;
            $certification->file_name = $file->getClientOriginalName();
        }

        $certification->save();

        return response()->json([
            'success' => true,
            'message' => 'Certification updated successfully',
            'certification' => [
                'id' => $certification->id,
                'name' => $certification->name,
                'certification_name' => $certification->certification_name,
                'expire_date' => $certification->expire_date?->format('Y-m-d'),
                'expire_date_formatted' => $certification->expire_date?->format('M j, Y'),
                'reminder_days' => $certification->reminder_days,
                'notes' => $certification->notes,
                'file_url' => $certification->file_url,
                'file_name' => $certification->file_name,
                'status_label' => $certification->status_label,
                'status_badge_class' => $certification->status_badge_class,
                'is_expired' => $certification->isExpired(),
            ],
        ]);
    }

    /**
     * Delete a certification.
     */
    public function deleteCertification($id)
    {
        $host = auth()->user()->host;

        $certification = \App\Models\StudioCertification::where('host_id', $host->id)
            ->findOrFail($id);

        // Delete associated file
        if ($certification->file_path) {
            try {
                \Storage::disk(config('filesystems.uploads'))->delete($certification->file_path);
            } catch (\Exception $e) {
                // Ignore deletion errors
            }
        }

        $certification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Certification deleted successfully',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Translations Management
    // ─────────────────────────────────────────────────────────────

    /**
     * Display translation management page.
     */
    public function translations(Request $request)
    {
        $host = auth()->user()->currentHost() ?? auth()->user()->host;


        $query = \App\Models\Translation::where('host_id', $host->id);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by page context (use 'page_filter' to avoid conflict with pagination 'page')
        if ($request->filled('page_filter')) {
            $query->where('page_context', $request->page_filter);
        }

        // Search by key or value
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('translation_key', 'like', "%{$search}%")
                  ->orWhere('value_en', 'like', "%{$search}%");
            });
        }

        $allTranslations = $query->orderBy('category')
            ->orderBy('page_context')
            ->orderBy('translation_key')
            ->get();

        // Manual pagination to avoid pagination issues
        $page = $request->get('page', 1);
        $perPage = 25;
        $translations = new \Illuminate\Pagination\LengthAwarePaginator(
            $allTranslations->forPage($page, $perPage)->values(),
            $allTranslations->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Get unique page contexts for filter dropdown
        $pageContexts = \App\Models\Translation::where('host_id', $host->id)
            ->whereNotNull('page_context')
            ->distinct()
            ->pluck('page_context');

        return view('host.settings.translations.index', [
            'host' => $host,
            'translations' => $translations,
            'categories' => \App\Models\Translation::getCategoryLabels(),
            'languages' => \App\Models\Translation::getSupportedLanguages(),
            'pageContexts' => $pageContexts,
            'filters' => [
                'category' => $request->category,
                'page_filter' => $request->page_filter,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a new translation.
     */
    public function storeTranslation(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            'category' => 'required|string|in:field_labels,page_titles,general_content,buttons,messages',
            'translation_key' => 'required|string|max:255',
            'value_en' => 'required|string',
            'value_fr' => 'nullable|string',
            'value_de' => 'nullable|string',
            'value_es' => 'nullable|string',
            'page_context' => 'nullable|string|max:100',
        ]);

        // Check for duplicate key
        $exists = \App\Models\Translation::where('host_id', $host->id)
            ->where('translation_key', $validated['translation_key'])
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A translation with this key already exists.',
            ], 422);
        }

        $translation = \App\Models\Translation::create([
            'host_id' => $host->id,
            'category' => $validated['category'],
            'translation_key' => $validated['translation_key'],
            'value_en' => $validated['value_en'],
            'value_fr' => $validated['value_fr'] ?? null,
            'value_de' => $validated['value_de'] ?? null,
            'value_es' => $validated['value_es'] ?? null,
            'page_context' => $validated['page_context'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Translation created successfully',
            'translation' => $translation,
        ]);
    }

    /**
     * Update an existing translation.
     */
    public function updateTranslation(Request $request, $id)
    {
        $host = auth()->user()->host;

        $translation = \App\Models\Translation::where('host_id', $host->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'category' => 'sometimes|string|in:field_labels,page_titles,general_content,buttons,messages',
            'translation_key' => 'sometimes|string|max:255',
            'value_en' => 'sometimes|string',
            'value_fr' => 'nullable|string',
            'value_de' => 'nullable|string',
            'value_es' => 'nullable|string',
            'page_context' => 'nullable|string|max:100',
            'is_active' => 'sometimes|boolean',
        ]);

        // Check for duplicate key if key is being changed
        if (isset($validated['translation_key']) && $validated['translation_key'] !== $translation->translation_key) {
            $exists = \App\Models\Translation::where('host_id', $host->id)
                ->where('translation_key', $validated['translation_key'])
                ->where('id', '!=', $id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'A translation with this key already exists.',
                ], 422);
            }
        }

        $translation->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Translation updated successfully',
            'translation' => $translation,
        ]);
    }

    /**
     * Delete a translation.
     */
    public function deleteTranslation($id)
    {
        $host = auth()->user()->host;

        $translation = \App\Models\Translation::where('host_id', $host->id)
            ->findOrFail($id);

        $translation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Translation deleted successfully',
        ]);
    }
}
