<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\WaitlistThankYouMail;
use App\Models\ProspectWaitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProspectWaitlistController extends Controller
{
    public function show()
    {
        return view('public.waitlist.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:prospect_waitlists,email',
            'studio_name' => 'nullable|string|max:255',
            'studio_type' => 'nullable|array',
            'studio_type.*' => 'string|in:' . implode(',', array_keys(ProspectWaitlist::STUDIO_TYPES)),
            'member_size' => 'nullable|string|in:' . implode(',', array_keys(ProspectWaitlist::MEMBER_SIZES)),
        ]);

        $waitlistEntry = ProspectWaitlist::create($validated);

        // Send thank you email
        try {
            Mail::to($waitlistEntry->email)->send(new WaitlistThankYouMail($waitlistEntry));
        } catch (\Exception $e) {
            // Log the error but don't fail the form submission
            \Log::error('Failed to send waitlist thank you email: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Thank you for joining our waitlist!']);
        }

        return redirect()->route('public.waitlist.success');
    }

    public function success()
    {
        return view('public.waitlist.success');
    }
}
