<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    use HasFactory;

    // Categories for translations
    const CATEGORY_FIELD_LABELS = 'field_labels';
    const CATEGORY_PAGE_TITLES = 'page_titles';
    const CATEGORY_GENERAL = 'general_content';
    const CATEGORY_BUTTONS = 'buttons';
    const CATEGORY_MESSAGES = 'messages';

    // Supported languages
    const LANG_ENGLISH = 'en';
    const LANG_FRENCH = 'fr';
    const LANG_GERMAN = 'de';
    const LANG_SPANISH = 'es';

    protected $fillable = [
        'host_id',
        'category',
        'translation_key',
        'value_en',
        'value_fr',
        'value_de',
        'value_es',
        'page_context',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    /**
     * Filter by host
     */
    public function scopeForHost($query, int $hostId)
    {
        return $query->where('host_id', $hostId);
    }

    /**
     * Filter by category
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Filter by page context
     */
    public function scopeForPageContext($query, string $pageContext)
    {
        return $query->where('page_context', $pageContext);
    }

    /**
     * Only active translations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    /**
     * Get the translated value for a specific locale
     */
    public function getValueForLocale(string $locale): string
    {
        $column = 'value_' . $locale;

        // Return the translated value if it exists, otherwise fallback to English
        return $this->{$column} ?? $this->value_en;
    }

    /**
     * Get all category labels
     */
    public static function getCategoryLabels(): array
    {
        return [
            self::CATEGORY_FIELD_LABELS => 'Field Labels',
            self::CATEGORY_PAGE_TITLES => 'Page Titles',
            self::CATEGORY_GENERAL => 'General Content',
            self::CATEGORY_BUTTONS => 'Buttons',
            self::CATEGORY_MESSAGES => 'Messages',
        ];
    }

    /**
     * Get all supported languages
     */
    public static function getSupportedLanguages(): array
    {
        return [
            self::LANG_ENGLISH => 'English',
            self::LANG_FRENCH => 'French',
            self::LANG_GERMAN => 'German',
            self::LANG_SPANISH => 'Spanish',
        ];
    }

    /**
     * Get language flag emoji
     */
    public static function getLanguageFlags(): array
    {
        return [
            self::LANG_ENGLISH => '🇺🇸',
            self::LANG_FRENCH => '🇫🇷',
            self::LANG_GERMAN => '🇩🇪',
            self::LANG_SPANISH => '🇪🇸',
        ];
    }

    /**
     * Check if translation is complete (all languages have values)
     */
    public function isComplete(): bool
    {
        return !empty($this->value_en)
            && !empty($this->value_fr)
            && !empty($this->value_de)
            && !empty($this->value_es);
    }

    /**
     * Get completion status as percentage
     */
    public function getCompletionPercentageAttribute(): int
    {
        $total = 4; // Total languages
        $filled = 0;

        if (!empty($this->value_en)) $filled++;
        if (!empty($this->value_fr)) $filled++;
        if (!empty($this->value_de)) $filled++;
        if (!empty($this->value_es)) $filled++;

        return (int) (($filled / $total) * 100);
    }

    /**
     * Get missing languages
     */
    public function getMissingLanguagesAttribute(): array
    {
        $missing = [];

        if (empty($this->value_en)) $missing[] = 'English';
        if (empty($this->value_fr)) $missing[] = 'French';
        if (empty($this->value_de)) $missing[] = 'German';
        if (empty($this->value_es)) $missing[] = 'Spanish';

        return $missing;
    }
}
