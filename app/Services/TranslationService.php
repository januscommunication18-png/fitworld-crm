<?php

namespace App\Services;

use App\Models\Host;
use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    protected ?Host $host = null;
    protected string $locale = 'en';
    protected array $translations = [];

    /**
     * Set the host for translations.
     */
    public function forHost(Host $host): self
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the locale for translations.
     */
    public function locale(string $locale): self
    {
        if (in_array($locale, ['en', 'fr', 'de', 'es'])) {
            $this->locale = $locale;
        }
        return $this;
    }

    /**
     * Get all translations for the current host and locale.
     * Global translations (host_id = NULL) are used as fallbacks.
     */
    public function all(): array
    {
        if (!$this->host) {
            return [];
        }

        $cacheKey = "translations.{$this->host->id}.{$this->locale}";

        return Cache::remember($cacheKey, 3600, function () {
            // First, get global translations as fallbacks
            $globalTranslations = Translation::whereNull('host_id')
                ->where('is_active', true)
                ->get();

            $result = [];
            foreach ($globalTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            // Then, get host-specific translations (these override globals)
            $hostTranslations = Translation::where('host_id', $this->host->id)
                ->where('is_active', true)
                ->get();

            foreach ($hostTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            return $result;
        });
    }

    /**
     * Get translations for a specific category.
     * Global translations are used as fallbacks.
     */
    public function forCategory(string $category): array
    {
        if (!$this->host) {
            return [];
        }

        $cacheKey = "translations.{$this->host->id}.{$this->locale}.{$category}";

        return Cache::remember($cacheKey, 3600, function () use ($category) {
            // First, get global translations for this category as fallbacks
            $globalTranslations = Translation::whereNull('host_id')
                ->where('category', $category)
                ->where('is_active', true)
                ->get();

            $result = [];
            foreach ($globalTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            // Then, get host-specific translations (these override globals)
            $hostTranslations = Translation::where('host_id', $this->host->id)
                ->where('category', $category)
                ->where('is_active', true)
                ->get();

            foreach ($hostTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            return $result;
        });
    }

    /**
     * Get translations for a specific page context.
     * Global translations are used as fallbacks.
     */
    public function forPage(string $pageContext): array
    {
        if (!$this->host) {
            return [];
        }

        $cacheKey = "translations.{$this->host->id}.{$this->locale}.page.{$pageContext}";

        return Cache::remember($cacheKey, 3600, function () use ($pageContext) {
            // First, get global translations for this page context as fallbacks
            $globalTranslations = Translation::whereNull('host_id')
                ->where('page_context', $pageContext)
                ->where('is_active', true)
                ->get();

            $result = [];
            foreach ($globalTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            // Then, get host-specific translations (these override globals)
            $hostTranslations = Translation::where('host_id', $this->host->id)
                ->where('page_context', $pageContext)
                ->where('is_active', true)
                ->get();

            foreach ($hostTranslations as $translation) {
                $result[$translation->translation_key] = $translation->getValueForLocale($this->locale);
            }

            return $result;
        });
    }

    /**
     * Get a single translation by key.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        if (!$this->host) {
            return $default;
        }

        $translations = $this->all();
        return $translations[$key] ?? $default;
    }

    /**
     * Translate a value using a key, with optional placeholders.
     *
     * Example: $service->translate('greeting', ['name' => 'John'])
     * For translation "Hello, :name!" returns "Hello, John!"
     */
    public function translate(string $key, array $replacements = [], ?string $default = null): string
    {
        $value = $this->get($key, $default ?? $key);

        foreach ($replacements as $placeholder => $replacement) {
            $value = str_replace(":$placeholder", $replacement, $value);
        }

        return $value;
    }

    /**
     * Check if a translation exists for a key.
     */
    public function has(string $key): bool
    {
        if (!$this->host) {
            return false;
        }

        $translations = $this->all();
        return isset($translations[$key]);
    }

    /**
     * Clear the translation cache for a host.
     * If hostId is null, clears global cache and ALL host caches (since they depend on globals).
     */
    public function clearCache(?int $hostId = null): void
    {
        $hostId = $hostId ?? $this->host?->id;

        // If clearing global translations, we need to clear ALL host caches
        if (!$hostId) {
            $this->clearGlobalCache();
            return;
        }

        // Clear all locale caches for this specific host
        $this->clearHostCache($hostId);
    }

    /**
     * Clear cache for a specific host.
     */
    protected function clearHostCache(int $hostId): void
    {
        foreach (['en', 'fr', 'de', 'es'] as $locale) {
            Cache::forget("translations.{$hostId}.{$locale}");

            // Clear category caches
            foreach (Translation::getCategoryLabels() as $category => $label) {
                Cache::forget("translations.{$hostId}.{$locale}.{$category}");
            }

            // Clear page context caches (we need to query for existing page contexts)
            $pageContexts = Translation::where(function($q) use ($hostId) {
                    $q->where('host_id', $hostId)->orWhereNull('host_id');
                })
                ->whereNotNull('page_context')
                ->distinct()
                ->pluck('page_context');

            foreach ($pageContexts as $pageContext) {
                Cache::forget("translations.{$hostId}.{$locale}.page.{$pageContext}");
            }
        }
    }

    /**
     * Clear global cache and ALL host caches (since they depend on globals).
     */
    protected function clearGlobalCache(): void
    {
        // Get all host IDs to clear their caches
        $hostIds = Host::pluck('id');

        foreach ($hostIds as $hostId) {
            $this->clearHostCache($hostId);
        }
    }

    /**
     * Get the default locale for a context (app or booking).
     */
    public static function getDefaultLocale(Host $host, string $context = 'app'): string
    {
        if ($context === 'booking') {
            return $host->default_language_booking ?? 'en';
        }

        return $host->default_language_app ?? 'en';
    }

    /**
     * Get all supported languages.
     */
    public static function getSupportedLanguages(): array
    {
        return Translation::getSupportedLanguages();
    }

    /**
     * Get language info with flags.
     */
    public static function getLanguagesWithFlags(): array
    {
        return [
            'en' => ['name' => 'English', 'flag' => '🇺🇸'],
            'fr' => ['name' => 'French', 'flag' => '🇫🇷'],
            'de' => ['name' => 'German', 'flag' => '🇩🇪'],
            'es' => ['name' => 'Spanish', 'flag' => '🇪🇸'],
        ];
    }

    /**
     * Create a new instance for a host with a specific locale.
     */
    public static function make(Host $host, ?string $locale = null): self
    {
        $service = new self();
        $service->forHost($host);

        if ($locale) {
            $service->locale($locale);
        } else {
            $service->locale($host->default_language_app ?? 'en');
        }

        return $service;
    }
}
