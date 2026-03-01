<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\ProspectWaitlist;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function index()
    {
        $waitlists = ProspectWaitlist::orderBy('created_at', 'desc')->get();

        return view('backoffice.waitlist.index', compact('waitlists'));
    }

    public function create()
    {
        return view('backoffice.waitlist.create');
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

        ProspectWaitlist::create($validated);

        return redirect()->route('backoffice.waitlist.index')
            ->with('success', 'Waitlist entry added successfully.');
    }

    public function destroy(ProspectWaitlist $waitlist)
    {
        $waitlist->delete();

        return redirect()->route('backoffice.waitlist.index')
            ->with('success', 'Waitlist entry deleted successfully.');
    }
}
