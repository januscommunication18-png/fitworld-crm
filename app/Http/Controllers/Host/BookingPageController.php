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
     * Update booking page settings (section-based)
     */
    public function update(Request $request)
    {
        $host = auth()->user()->host;
        $section = $request->input('section', 'all');

        $rules = $this->sectionRules($section);
        $validated = $request->validate($rules);

        $currentSettings = $host->booking_settings ?? [];

        switch ($section) {
            case 'publish_status':
                $host->booking_page_status = $validated['booking_page_status'];
                $host->save();
                $label = 'Publish status';
                break;

            case 'branding':
                $currentSettings['display_name'] = $validated['display_name'];
                $currentSettings['primary_color'] = $validated['primary_color'];
                $currentSettings['theme'] = $validated['theme'];
                $currentSettings['font'] = $validated['font'];
                $host->booking_settings = $currentSettings;
                $host->save();
                $label = 'Branding';
                break;

            case 'public_content':
                $aboutText = $validated['about_text'] ?? null;
                if ($aboutText) {
                    $aboutText = strip_tags($aboutText, '<p><br><strong><em><u><s><ol><ul><li><a><span><h1><h2><h3><h4><blockquote>');
                }
                $host->show_address = $request->boolean('show_address');
                $host->show_social_links = $request->boolean('show_social_links');
                $currentSettings['about_text'] = $aboutText;
                $currentSettings['show_instructors'] = $request->boolean('show_instructors');
                $currentSettings['show_amenities'] = $request->boolean('show_amenities');
                $currentSettings['location_display'] = $validated['location_display'];
                $host->booking_settings = $currentSettings;
                $host->save();
                $label = 'Public content';
                break;

            case 'booking_ux':
                $currentSettings['default_view'] = $validated['default_view'];
                $currentSettings['show_class_descriptions'] = $request->boolean('show_class_descriptions');
                $currentSettings['show_instructor_photos'] = $request->boolean('show_instructor_photos');
                $currentSettings['allow_waitlist'] = $request->boolean('allow_waitlist');
                $currentSettings['require_account'] = $request->boolean('require_account');
                $host->booking_settings = $currentSettings;
                $host->save();
                $label = 'Booking experience';
                break;

            case 'filters':
                $currentSettings['filter_class_type'] = $request->boolean('filter_class_type');
                $currentSettings['filter_instructor'] = $request->boolean('filter_instructor');
                $currentSettings['filter_location'] = $request->boolean('filter_location');
                $host->booking_settings = $currentSettings;
                $host->save();
                $label = 'Filters';
                break;

            default:
                $label = 'Settings';
                break;
        }

        return redirect()->route('settings.locations.booking-page')
            ->with('success', "{$label} updated successfully");
    }

    private function sectionRules(string $section): array
    {
        $base = ['section' => 'nullable|string'];

        return match ($section) {
            'publish_status' => $base + [
                'booking_page_status' => 'required|in:draft,published',
            ],
            'branding' => $base + [
                'display_name' => 'nullable|string|max:255',
                'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
                'theme' => 'required|in:light,dark,auto',
                'font' => 'required|string|max:50',
            ],
            'public_content' => $base + [
                'about_text' => 'nullable|string|max:2000',
                'show_address' => 'boolean',
                'show_social_links' => 'boolean',
                'show_instructors' => 'boolean',
                'show_amenities' => 'boolean',
                'location_display' => 'required|in:auto,single,multi',
            ],
            'booking_ux' => $base + [
                'default_view' => 'required|in:calendar,list',
                'show_class_descriptions' => 'boolean',
                'show_instructor_photos' => 'boolean',
                'allow_waitlist' => 'boolean',
                'require_account' => 'boolean',
            ],
            'filters' => $base + [
                'filter_class_type' => 'boolean',
                'filter_instructor' => 'boolean',
                'filter_location' => 'boolean',
            ],
            default => $base,
        };
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

        // Delete old logo if exists (try-catch for cloud storage compatibility)
        if ($host->logo_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($host->logo_path);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        $path = $request->file('logo')->storePublicly($host->getStoragePath('branding'), config('filesystems.uploads'));
        $host->logo_path = $path;
        $host->save();

        return response()->json([
            'success' => true,
            'path' => Storage::disk(config('filesystems.uploads'))->url($path),
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

        // Delete old cover if exists (try-catch for cloud storage compatibility)
        if ($host->cover_image_path) {
            try {
                Storage::disk(config('filesystems.uploads'))->delete($host->cover_image_path);
            } catch (\Exception $e) {
                // Ignore deletion errors (file may not exist or be on different storage)
            }
        }

        $path = $request->file('cover')->storePublicly($host->getStoragePath('branding'), config('filesystems.uploads'));
        $host->cover_image_path = $path;
        $host->save();

        return response()->json([
            'success' => true,
            'path' => Storage::disk(config('filesystems.uploads'))->url($path),
        ]);
    }

    /**
     * Remove logo
     */
    public function removeLogo()
    {
        $host = auth()->user()->host;

        if ($host->logo_path && Storage::disk(config('filesystems.uploads'))->exists($host->logo_path)) {
            Storage::disk(config('filesystems.uploads'))->delete($host->logo_path);
        }

        $host->logo_path = null;
        $host->save();

        return response()->json(['success' => true]);
    }

    /**
     * Update cover image position (vertical offset)
     */
    public function updateCoverPosition(Request $request)
    {
        $request->validate([
            'position_y' => 'required|numeric|min:0|max:100',
        ]);

        $host = auth()->user()->host;
        $settings = $host->booking_settings ?? [];
        $settings['cover_position_y'] = round($request->position_y, 1);
        $host->booking_settings = $settings;
        $host->save();

        return response()->json(['success' => true]);
    }

    /**
     * Remove cover image
     */
    public function removeCover()
    {
        $host = auth()->user()->host;

        if ($host->cover_image_path && Storage::disk(config('filesystems.uploads'))->exists($host->cover_image_path)) {
            Storage::disk(config('filesystems.uploads'))->delete($host->cover_image_path);
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
