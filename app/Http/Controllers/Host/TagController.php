<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TagController extends Controller
{
    protected function getHost()
    {
        return Auth::user()->currentHost() ?? Auth::user()->host;
    }

    /**
     * Display a listing of tags.
     */
    public function index(Request $request)
    {
        $host = $this->getHost();

        $query = Tag::forHost($host->id);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $tags = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('host.clients.tags.index', [
            'tags' => $tags,
            'defaultColors' => Tag::getDefaultColors(),
        ]);
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request)
    {
        $host = $this->getHost();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $slug = Str::slug($validated['name']);

        // Ensure unique slug for this host
        $originalSlug = $slug;
        $counter = 1;
        while (Tag::forHost($host->id)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        Tag::create([
            'host_id' => $host->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'color' => $validated['color'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tag created successfully.']);
        }

        return back()->with('success', 'Tag created successfully.');
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, Tag $tag)
    {
        $this->authorizeTag($tag);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tag updated successfully.']);
        }

        return back()->with('success', 'Tag updated successfully.');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag)
    {
        $this->authorizeTag($tag);

        $tag->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Tag deleted successfully.']);
        }

        return back()->with('success', 'Tag deleted successfully.');
    }

    /**
     * Authorize that the tag belongs to the current host.
     */
    protected function authorizeTag(Tag $tag): void
    {
        $host = $this->getHost();
        if ($tag->host_id !== $host->id) {
            abort(403, 'Unauthorized');
        }
    }
}
