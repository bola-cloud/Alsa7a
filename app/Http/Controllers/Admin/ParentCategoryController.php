<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ParentCategoryController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $parentCategories = ParentCategory::latest()->paginate(10);
        return view('admin.parent_categories.index', compact('parentCategories'));
    }

    public function create()
    {
        return view('admin.parent_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'nullable|image',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
        ]);

        $categoryData = [
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageService->upload($request->file('image'), 'parent_categories');
        }

        ParentCategory::create($categoryData);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.parent_categories.index');
    }

    public function edit(ParentCategory $parentCategory)
    {
        return view('admin.parent_categories.edit', compact('parentCategory'));
    }

    public function update(Request $request, ParentCategory $parentCategory)
    {
        $data = $request->validate([
            'image' => 'nullable|image',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
        ]);

        $categoryData = [
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageService->replace(
                $request->file('image'),
                'parent_categories',
                $parentCategory->image
            );
        }

        $parentCategory->update($categoryData);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.parent_categories.index');
    }

    public function destroy(ParentCategory $parentCategory)
    {
        if ($parentCategory->isProtected()) {
            return redirect()->back()->with('swal_error', 'System sections cannot be deleted.');
        }

        $this->imageService->delete($parentCategory->image);
        $parentCategory->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.parent_categories.index');
    }
}
