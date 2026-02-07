<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BookingPageController extends Controller
{
    /**
     * Show booking page settings form
     */
    public function index()
    {
        $host = auth()->user()->host;
        $settings = array_merge(Host::defaultBookingSettings(), $host->booking_settings ?? []);
        $locations = $host->locations()->orderBy('name')->get();
        $instructors = $host->instructors()->orderBy('name')->get();

        return view('host.settings.locations.booking-page', [
            'host' => $host,
            'settings' => $settings,
            'locations' => $locations,
            'instructors' => $instructors,
            'fonts' => $this->getFonts(),
            'themes' => $this->getThemes(),
        ]);
    }

    /**
     * Update booking page settings
     */
    public function update(Request $request)
    {
        $host = auth()->user()->host;

        $validated = $request->validate([
            // Branding
            'display_name' => 'nullable|string|max:255',
            'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'theme' => 'required|in:light,dark,auto',
            'font' => 'required|string|max:50',

            // Public Content
            'about_text' => 'nullable|string|max:2000',
            'show_instructors' => 'boolean',
            'show_amenities' => 'boolean',
            'location_display' => 'required|in:auto,single,multi',

            // Booking UX
            'default_view' => 'required|in:calendar,list',
            'show_class_descriptions' => 'boolean',
            'show_instructor_photos' => 'boolean',
            'allow_waitlist' => 'boolean',
            'require_account' => 'boolean',

            // Filters
            'filter_class_type' => 'boolean',
            'filter_instructor' => 'boolean',
            'filter_location' => 'boolean',
        ]);

        // Convert checkbox values to booleans
        $booleanFields = [
            'show_instructors', 'show_amenities',
            'show_class_descriptions', 'show_instructor_photos',
            'allow_waitlist', 'require_account',
            'filter_class_type', 'filter_instructor', 'filter_location',
        ];

        foreach ($booleanFields as $field) {
            $validated[$field] = $request->boolean($field);
        }

        // Merge with existing settings
        $currentSettings = $host->booking_settings ?? [];
        $host->booking_settings = array_merge($currentSettings, $validated);
        $host->save();

        return redirect()->route('settings.locations.booking-page')
            ->with('success', 'Booking page settings updated successfully');
    }

    /**
     * Upload booking page logo
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,gif,svg,webp|max:2048',
        ]);

        $host = auth()->user()->host;

        // Delete old logo if exists
        if ($host->logo_path && Storage::disk('public')->exists($host->logo_path)) {
            Storage::disk('public')->delete($host->logo_path);
        }

        $path = $request->file('logo')->store('hosts/' . $host->id . '/branding', 'public');
        $host->logo_path = $path;
        $host->save();

        return response()->json([
            'success' => true,
            'path' => Storage::url($path),
        ]);
    }

    /**
     * Upload booking page cover image
     */
    public function uploadCover(Request $request)
    {
        $request->validate([
            'cover' => 'required|image|mimes:jpeg,png,webp|max:5120',
        ]);

        $host = auth()->user()->host;

        // Delete old cover if exists
        if ($host->cover_image_path && Storage::disk('public')->exists($host->cover_image_path)) {
            Storage::disk('public')->delete($host->cover_image_path);
        }

        $path = $request->file('cover')->store('hosts/' . $host->id . '/branding', 'public');
        $host->cover_image_path = $path;
        $host->save();

        return response()->json([
            'success' => true,
            'path' => Storage::url($path),
        ]);
    }

    /**
     * Remove logo
     */
    public function removeLogo()
    {
        $host = auth()->user()->host;

        if ($host->logo_path && Storage::disk('public')->exists($host->logo_path)) {
            Storage::disk('public')->delete($host->logo_path);
        }

        $host->logo_path = null;
        $host->save();

        return response()->json(['success' => true]);
    }

    /**
     * Remove cover image
     */
    public function removeCover()
    {
        $host = auth()->user()->host;

        if ($host->cover_image_path && Storage::disk('public')->exists($host->cover_image_path)) {
            Storage::disk('public')->delete($host->cover_image_path);
        }

        $host->cover_image_path = null;
        $host->save();

        return response()->json(['success' => true]);
    }

    /**
     * Available fonts
     */
    private function getFonts(): array
    {
        return [
            'inter' => 'Inter',
            'roboto' => 'Roboto',
            'open-sans' => 'Open Sans',
            'lato' => 'Lato',
            'poppins' => 'Poppins',
            'montserrat' => 'Montserrat',
        ];
    }

    /**
     * Available themes
     */
    private function getThemes(): array
    {
        return [
            'light' => 'Light',
            'dark' => 'Dark',
            'auto' => 'Auto (System)',
        ];
    }
}
