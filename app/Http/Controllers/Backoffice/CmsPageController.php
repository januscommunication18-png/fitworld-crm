<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CmsPageController extends Controller
{
    /**
     * Display a listing of CMS pages.
     */
    public function index(Request $request)
    {
        $type = $request->get('type');

        $query = CmsPage::query()
            ->orderByRaw("FIELD(status, 'active', 'draft', 'inactive')")
            ->orderBy('type')
            ->orderBy('updated_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        $pages = $query->get();

        // Group pages by type for display
        $pagesByType = $pages->groupBy('type');

        return view('backoffice.cms.index', compact('pages', 'pagesByType', 'type'));
    }

    /**
     * Show the form for creating a new CMS page.
     */
    public function create(Request $request)
    {
        $page = new CmsPage();
        $page->type = $request->get('type', CmsPage::TYPE_TERMS_CONDITIONS);
        $page->status = CmsPage::STATUS_DRAFT;

        return view('backoffice.cms.form', [
            'page' => $page,
            'isEdit' => false,
        ]);
    }

    /**
     * Store a newly created CMS page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(CmsPage::getTypes()))],
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => ['required', Rule::in(array_keys(CmsPage::getStatuses()))],
        ]);

        $validated['slug'] = Str::slug($validated['title']) . '-' . Str::random(6);
        $validated['created_by'] = Auth::guard('admin')->id();
        $validated['updated_by'] = Auth::guard('admin')->id();

        if ($validated['status'] === CmsPage::STATUS_ACTIVE) {
            $validated['published_at'] = now();
        }

        $page = CmsPage::create($validated);

        return redirect()
            ->route('backoffice.cms.index')
            ->with('success', 'Page created successfully.');
    }

    /**
     * Show the form for editing the specified CMS page.
     */
    public function edit(CmsPage $cmsPage)
    {
        return view('backoffice.cms.form', [
            'page' => $cmsPage,
            'isEdit' => true,
        ]);
    }

    /**
     * Update the specified CMS page.
     */
    public function update(Request $request, CmsPage $cmsPage)
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(CmsPage::getTypes()))],
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => ['required', Rule::in(array_keys(CmsPage::getStatuses()))],
        ]);

        $validated['updated_by'] = Auth::guard('admin')->id();

        // If changing to active and wasn't active before, set published_at
        if ($validated['status'] === CmsPage::STATUS_ACTIVE && $cmsPage->status !== CmsPage::STATUS_ACTIVE) {
            $validated['published_at'] = now();
        }

        $cmsPage->update($validated);

        return redirect()
            ->route('backoffice.cms.index')
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified CMS page.
     */
    public function destroy(CmsPage $cmsPage)
    {
        // Don't allow deleting active pages
        if ($cmsPage->isActive()) {
            return redirect()
                ->route('backoffice.cms.index')
                ->with('error', 'Cannot delete an active page. Please set it to inactive first.');
        }

        $cmsPage->delete();

        return redirect()
            ->route('backoffice.cms.index')
            ->with('success', 'Page deleted successfully.');
    }

    /**
     * Toggle the status of a CMS page.
     */
    public function toggleStatus(Request $request, CmsPage $cmsPage)
    {
        $newStatus = $request->input('status');

        if (!in_array($newStatus, array_keys(CmsPage::getStatuses()))) {
            return response()->json(['success' => false, 'message' => 'Invalid status.'], 400);
        }

        $cmsPage->status = $newStatus;
        $cmsPage->updated_by = Auth::guard('admin')->id();

        if ($newStatus === CmsPage::STATUS_ACTIVE && !$cmsPage->published_at) {
            $cmsPage->published_at = now();
        }

        $cmsPage->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
            'new_status' => $cmsPage->status,
            'status_label' => $cmsPage->status_label,
            'badge_class' => $cmsPage->status_badge_class,
        ]);
    }
}
