<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request)
    {
        $hostId = $request->get('host_id');
        $scope = $request->get('scope', 'studio'); // 'global' or 'studio'
        $hosts = Host::orderBy('studio_name')->get(['id', 'studio_name']);

        $query = Translation::query();

        // Filter by scope
        if ($scope === 'global') {
            $query->whereNull('host_id');
        } elseif ($scope === 'studio' && $hostId) {
            $query->where('host_id', $hostId);
        } elseif ($scope === 'studio' && !$hostId) {
            // No host selected in studio mode - return empty results
            $query->whereRaw('1 = 0');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by page context
        if ($request->filled('page_filter')) {
            $query->where('page_context', $request->page_filter);
        }

        // Search by key or value
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('translation_key', 'like', "%{$search}%")
                  ->orWhere('value_en', 'like', "%{$search}%");
            });
        }

        $translations = $query->orderBy('host_id')
            ->orderBy('category')
            ->orderBy('page_context')
            ->orderBy('translation_key')
            ->paginate(25)
            ->withQueryString();

        // Get unique page contexts for filter dropdown
        $pageContextsQuery = Translation::whereNotNull('page_context')->distinct();
        if ($scope === 'global') {
            $pageContextsQuery->whereNull('host_id');
        } elseif ($hostId) {
            $pageContextsQuery->where('host_id', $hostId);
        }
        $pageContexts = $pageContextsQuery->pluck('page_context');

        return view('backoffice.translations.index', [
            'hosts' => $hosts,
            'hostId' => $hostId,
            'scope' => $scope,
            'translations' => $translations,
            'categories' => Translation::getCategoryLabels(),
            'languages' => Translation::getSupportedLanguages(),
            'pageContexts' => $pageContexts,
            'filters' => [
                'category' => $request->category,
                'page_filter' => $request->page_filter,
                'search' => $request->search,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'host_id' => 'nullable|exists:hosts,id',
            'category' => 'required|string|in:field_labels,page_titles,general_content,buttons,messages',
            'translation_key' => 'required|string|max:255',
            'value_en' => 'required|string',
            'value_fr' => 'nullable|string',
            'value_de' => 'nullable|string',
            'value_es' => 'nullable|string',
            'page_context' => 'nullable|string|max:100',
        ]);

        // Check for duplicate key within the same scope (host or global)
        $query = Translation::where('translation_key', $validated['translation_key']);
        if ($validated['host_id']) {
            $query->where('host_id', $validated['host_id']);
        } else {
            $query->whereNull('host_id');
        }
        $exists = $query->exists();

        if ($exists) {
            $scope = $validated['host_id'] ? 'this host' : 'global translations';
            return response()->json([
                'success' => false,
                'message' => "A translation with this key already exists in {$scope}.",
            ], 422);
        }

        $translation = Translation::create($validated);

        // Clear cache for this host (or global cache)
        app(TranslationService::class)->clearCache($validated['host_id']);

        return response()->json([
            'success' => true,
            'message' => 'Translation created successfully.',
            'data' => $translation,
        ]);
    }

    public function update(Request $request, $id)
    {
        $translation = Translation::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string|in:field_labels,page_titles,general_content,buttons,messages',
            'translation_key' => 'required|string|max:255',
            'value_en' => 'required|string',
            'value_fr' => 'nullable|string',
            'value_de' => 'nullable|string',
            'value_es' => 'nullable|string',
            'page_context' => 'nullable|string|max:100',
        ]);

        // Check for duplicate key (excluding current translation)
        $exists = Translation::where('host_id', $translation->host_id)
            ->where('translation_key', $validated['translation_key'])
            ->where('id', '!=', $translation->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'A translation with this key already exists for this host.',
            ], 422);
        }

        $translation->update($validated);

        // Clear cache for this host
        app(TranslationService::class)->clearCache($translation->host_id);

        return response()->json([
            'success' => true,
            'message' => 'Translation updated successfully.',
            'data' => $translation,
        ]);
    }

    public function destroy($id)
    {
        $translation = Translation::findOrFail($id);
        $hostId = $translation->host_id;

        $translation->delete();

        // Clear cache for this host
        app(TranslationService::class)->clearCache($hostId);

        return response()->json([
            'success' => true,
            'message' => 'Translation deleted successfully.',
        ]);
    }

    /**
     * Copy translations from one host (or global) to another.
     */
    public function copy(Request $request)
    {
        $validated = $request->validate([
            'source_host_id' => 'required|string', // Can be numeric host ID or 'global'
            'target_host_id' => 'required|exists:hosts,id',
            'overwrite' => 'boolean',
        ]);

        // Determine source - either a host or global translations
        $isGlobalSource = $validated['source_host_id'] === 'global';

        if ($isGlobalSource) {
            $sourceTranslations = Translation::whereNull('host_id')->get();
        } else {
            // Validate that source_host_id exists if not 'global'
            if (!Host::find($validated['source_host_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source studio not found.',
                ], 422);
            }
            $sourceTranslations = Translation::where('host_id', $validated['source_host_id'])->get();
        }

        $copied = 0;
        $skipped = 0;

        foreach ($sourceTranslations as $source) {
            $exists = Translation::where('host_id', $validated['target_host_id'])
                ->where('translation_key', $source->translation_key)
                ->exists();

            if ($exists && !($validated['overwrite'] ?? false)) {
                $skipped++;
                continue;
            }

            Translation::updateOrCreate(
                [
                    'host_id' => $validated['target_host_id'],
                    'translation_key' => $source->translation_key,
                ],
                [
                    'category' => $source->category,
                    'page_context' => $source->page_context,
                    'value_en' => $source->value_en,
                    'value_fr' => $source->value_fr,
                    'value_de' => $source->value_de,
                    'value_es' => $source->value_es,
                    'is_active' => $source->is_active,
                ]
            );
            $copied++;
        }

        // Clear cache for target host
        app(TranslationService::class)->clearCache($validated['target_host_id']);

        $sourceLabel = $isGlobalSource ? 'global translations' : 'the selected studio';
        return response()->json([
            'success' => true,
            'message' => "Copied {$copied} translations from {$sourceLabel}. Skipped {$skipped} existing translations.",
        ]);
    }
}
