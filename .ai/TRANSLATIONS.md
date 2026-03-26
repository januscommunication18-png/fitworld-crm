# Translation System Documentation

This document explains how to use the translation system in FitCRM blade templates.

## Overview

FitCRM uses a database-driven translation system that supports multiple languages (English, French, German, Spanish). Translations are automatically loaded for all views in the `host.*`, `subdomain.*`, and layout directories via a View Composer.

## How It Works

1. **TranslationViewComposer** (`app/View/Composers/TranslationViewComposer.php`) automatically loads translations into all registered views
2. Translations are available in blade templates as the `$trans` array
3. Global translations (host_id = NULL) apply to all studios as defaults
4. Host-specific translations can override global translations

## Using Translations in Blade Templates

### Basic Usage

Always use the null coalescing operator (`??`) to provide a fallback:

```blade
{{ $trans['translation.key'] ?? 'Default English Text' }}
```

### With Placeholders

For translations with dynamic values, use `str_replace`:

```blade
{{ str_replace(':name', $user->name, $trans['greeting.welcome'] ?? 'Welcome, :name!') }}
```

### In Conditionals

```blade
@if($item['completed'])
    {{ $trans['status.completed'] ?? 'Completed' }}
@else
    {{ $trans['status.pending'] ?? 'Pending' }}
@endif
```

### Page Title Section

```blade
@section('title', $trans['page.my_page_title'] ?? 'My Page Title')
```

## Translation Key Naming Convention

Use dot notation with descriptive prefixes:

| Prefix | Usage | Example |
|--------|-------|---------|
| `nav.` | Navigation/sidebar items | `nav.dashboard`, `nav.settings` |
| `btn.` | Button labels | `btn.save`, `btn.cancel`, `btn.delete` |
| `page.` | Page titles | `page.clients`, `page.schedule` |
| `field.` | Form field labels | `field.email`, `field.phone` |
| `msg.` | Messages/notifications | `msg.success`, `msg.error` |
| `common.` | Common/shared text | `common.yes`, `common.no`, `common.loading` |
| `setup.` | Setup checklist | `setup.welcome`, `setup.done` |
| `booking.` | Booking related | `booking.confirm`, `booking.cancel` |
| `schedule.` | Schedule related | `schedule.calendar`, `schedule.add_class` |

## Adding New Translations

### Step 1: Add to GlobalTranslationsSeeder

Edit `database/seeders/GlobalTranslationsSeeder.php`:

1. Find the appropriate method (e.g., `getNavigationTranslations()`, `getButtonTranslations()`)
2. Add your translation entry:

```php
['translation_key' => 'your.translation_key', 'category' => 'general_content', 'page_context' => 'your_page',
 'value_en' => 'English text', 'value_fr' => 'French text', 'value_de' => 'German text', 'value_es' => 'Spanish text'],
```

### Step 2: Create a New Method (for new page/feature)

If adding translations for a new feature, create a new method:

```php
/**
 * Your Feature translations.
 */
protected function getYourFeatureTranslations(): array
{
    return [
        ['translation_key' => 'feature.title', 'category' => 'general_content', 'page_context' => 'your_feature',
         'value_en' => 'Feature Title', 'value_fr' => 'Titre de la fonctionnalité', 'value_de' => 'Funktionstitel', 'value_es' => 'Título de la función'],
        // Add more translations...
    ];
}
```

Then add it to the `getTranslations()` method:

```php
protected function getTranslations(): array
{
    return array_merge(
        // ... existing methods
        $this->getYourFeatureTranslations(),
    );
}
```

### Step 3: Run the Seeder

```bash
php artisan db:seed --class=GlobalTranslationsSeeder
php artisan cache:clear
```

## Translation Categories

| Category | Description |
|----------|-------------|
| `general_content` | General page content, labels, descriptions |
| `buttons` | Button text |
| `form_labels` | Form field labels |
| `validation` | Validation error messages |
| `notifications` | Toast/alert messages |

## Page Context

The `page_context` field helps organize translations by feature/page:

- `sidebar` - Sidebar navigation
- `dashboard` - Main dashboard
- `setup_checklist` - Setup wizard
- `schedule` - Calendar/schedule
- `clients` - Client management
- `settings` - Settings pages
- `walk_in` - Walk-in booking
- `null` - Global/shared translations

## Best Practices

### DO:
- Always provide a fallback with `??`
- Use descriptive, consistent key names
- Group related translations with common prefixes
- Include all 4 languages when adding translations
- Clear cache after running seeders

### DON'T:
- Use raw text without translation support in new views
- Create duplicate translation keys
- Use deeply nested key names (max 3 levels: `prefix.feature.item`)
- Forget to run the seeder after adding new translations

## Checklist for New Blade Views

When creating a new blade view, ensure:

- [ ] All visible text uses `$trans['key'] ?? 'Fallback'` syntax
- [ ] Page title uses translation: `@section('title', $trans['page.xxx'] ?? 'Title')`
- [ ] Button labels use `btn.` prefix translations
- [ ] Form labels use `field.` prefix translations
- [ ] Status messages use `msg.` prefix translations
- [ ] Translations are added to `GlobalTranslationsSeeder.php`
- [ ] Seeder has been run: `php artisan db:seed --class=GlobalTranslationsSeeder`
- [ ] Cache has been cleared: `php artisan cache:clear`

## Clearing Translation Cache

Translations are cached for 1 hour. To see changes immediately:

```bash
php artisan cache:clear
```

Or programmatically:

```php
app(TranslationService::class)->clearCache();
```

## Testing Translations

To verify translations are loaded correctly:

```bash
php artisan tinker
>>> $host = \App\Models\Host::first();
>>> $service = \App\Services\TranslationService::make($host, 'en');
>>> $service->get('your.translation_key');
```

## Translation Service API

The `TranslationService` class provides these methods:

```php
// Get all translations
$trans = TranslationService::make($host, 'en')->all();

// Get single translation
$value = TranslationService::make($host, 'en')->get('key', 'default');

// Get translations for a category
$buttons = TranslationService::make($host, 'en')->forCategory('buttons');

// Get translations for a page context
$setup = TranslationService::make($host, 'en')->forPage('setup_checklist');

// Clear cache
TranslationService::make($host)->clearCache();
```

## Supported Languages

| Code | Language |
|------|----------|
| `en` | English |
| `fr` | French |
| `de` | German |
| `es` | Spanish |
