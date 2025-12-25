<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'image' => 'nullable|image',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'is_service_provider' => 'nullable|boolean',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
        ]);

        $categoryData = [
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($categoryData);

        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.created'));
    }

    public function show(Category $category)
    {
        //
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'image' => 'nullable|image',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'is_service_provider' => 'nullable|boolean',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
        ]);

        $categoryData = [
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            if ($category->image_url) { // Assuming there might be a helper or check needed, but storage delete uses path
                // Deleting handled by disk in real app usually, simplistic here
            }
            $categoryData['image'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($categoryData);

        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.updated'));
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', __('admin.messages.deleted'));
    }
}
