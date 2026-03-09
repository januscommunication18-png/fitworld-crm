<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Rules\ValidName;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Display list of newsletter subscribers
     */
    public function index()
    {
        $subscribers = NewsletterSubscriber::orderBy('created_at', 'desc')->get();

        return view('backoffice.newsletter.index', [
            'subscribers' => $subscribers,
        ]);
    }

    /**
     * Show create subscriber form
     */
    public function create()
    {
        return view('backoffice.newsletter.create');
    }

    /**
     * Store a new subscriber
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['nullable', 'string', 'max:50', new ValidName],
            'email' => 'required|email|max:255|unique:newsletter_subscribers,email',
        ]);

        NewsletterSubscriber::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => strtolower($validated['email']),
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => NewsletterSubscriber::SOURCE_MANUAL,
            'subscribed_at' => now(),
        ]);

        return redirect()->route('backoffice.newsletter.index')
            ->with('success', 'Subscriber added successfully.');
    }

    /**
     * Toggle subscriber status
     */
    public function toggleStatus(NewsletterSubscriber $subscriber)
    {
        if ($subscriber->isActive()) {
            $subscriber->unsubscribe();
            $message = 'Subscriber has been unsubscribed.';
        } else {
            $subscriber->resubscribe();
            $message = 'Subscriber has been reactivated.';
        }

        return redirect()->route('backoffice.newsletter.index')
            ->with('success', $message);
    }

    /**
     * Delete a subscriber
     */
    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return redirect()->route('backoffice.newsletter.index')
            ->with('success', 'Subscriber deleted successfully.');
    }
}
