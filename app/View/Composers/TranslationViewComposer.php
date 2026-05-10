<?php

namespace App\View\Composers;

use App\Services\TranslationService;
use Illuminate\View\View;

class TranslationViewComposer
{
    /**
     * Bind translation data to the view.
     */
    public function compose(View $view): void
    {
        $host = $this->resolveHost();

        if (!$host) {
            $view->with('trans', []);
            return;
        }

        // Subdomain (public) pages use "language_{id}", studio portal uses "studio_language_{id}"
        $request = request();
        $isSubdomain = $request && $request->attributes->has('subdomain_host');
        $defaultLang = $isSubdomain
            ? ($host->default_language_booking ?? 'en')
            : ($host->default_language_app ?? 'en');

        $selectedLang = session("language_{$host->id}",
            session("studio_language_{$host->id}", $defaultLang)
        );
        $t = TranslationService::make($host, $selectedLang);

        $view->with('trans', $t->all());
    }

    /**
     * Resolve the host from various sources.
     */
    protected function resolveHost()
    {
        // 1. Try to get host from subdomain request attributes (public booking pages)
        $request = request();
        if ($request && $request->attributes->has('subdomain_host')) {
            return $request->attributes->get('subdomain_host');
        }

        // 2. Try to get host from authenticated user (studio portal)
        $user = auth()->user();
        if ($user) {
            return $user->currentHost() ?? $user->host;
        }

        // 3. Try to get host from authenticated member guard
        $member = auth('member')->user();
        if ($member) {
            return $member->host;
        }

        return null;
    }
}
