<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = CommunityCategory::latest()->paginate(10);
        return view('admin.community_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.community_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name_en', 'name_ar']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('community_categories', 'public');
        }

        CommunityCategory::create($data);

        return redirect()->route('admin.community_categories.index')
            ->with('success', __('admin.messages.created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CommunityCategory $communityCategory)
    {
        return view('admin.community_categories.edit', compact('communityCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CommunityCategory $communityCategory)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name_en', 'name_ar']);

        if ($request->hasFile('image')) {
            if ($communityCategory->image) {
                Storage::disk('public')->delete($communityCategory->image);
            }
            $data['image'] = $request->file('image')->store('community_categories', 'public');
        }

        $communityCategory->update($data);

        return redirect()->route('admin.community_categories.index')
            ->with('success', __('admin.messages.updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CommunityCategory $communityCategory)
    {
        if ($communityCategory->image) {
            Storage::disk('public')->delete($communityCategory->image);
        }
        $communityCategory->delete();

        return redirect()->route('admin.community_categories.index')
            ->with('success', __('admin.messages.deleted_successfully'));
    }
}
