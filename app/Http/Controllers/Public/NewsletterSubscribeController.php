<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Rules\ValidName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterSubscribeController extends Controller
{
    /**
     * Show the newsletter subscription form (embeddable)
     */
    public function form()
    {
        return view('public.newsletter.form');
    }

    /**
     * Handle form submission
     */
    public function store(Request $request)
    {
        // Rate limiting
        $key = 'newsletter-subscribe:' . ($request->ip() ?? 'unknown');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many attempts. Please try again in {$seconds} seconds.",
                ], 429);
            }

            return back()->withErrors(['email' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:50', new ValidName],
            'last_name' => ['nullable', 'string', 'max:50', new ValidName],
            'email' => 'required|email|max:255',
        ]);

        // Check if already subscribed
        $existing = NewsletterSubscriber::where('email', strtolower($validated['email']))->first();

        if ($existing) {
            if ($existing->isActive()) {
                // Already subscribed
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'You are already subscribed to our newsletter!',
                        'already_subscribed' => true,
                    ]);
                }
                return redirect()->route('public.newsletter.success')->with('already_subscribed', true);
            } else {
                // Resubscribe
                $existing->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'] ?? null,
                ]);
                $existing->resubscribe();

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Welcome back! You have been resubscribed.',
                    ]);
                }
                return redirect()->route('public.newsletter.success');
            }
        }

        // Create new subscriber
        NewsletterSubscriber::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'] ?? null,
            'email' => strtolower($validated['email']),
            'status' => NewsletterSubscriber::STATUS_ACTIVE,
            'source' => NewsletterSubscriber::SOURCE_EMBED_FORM,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'subscribed_at' => now(),
        ]);

        RateLimiter::hit($key, 3600); // 1 hour

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing!',
            ]);
        }

        return redirect()->route('public.newsletter.success');
    }

    /**
     * Success page
     */
    public function success()
    {
        return view('public.newsletter.success');
    }
}
