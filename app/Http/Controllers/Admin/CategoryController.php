<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

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
            'name' => $data['name']['en'],
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'description' => $data['description']['en'] ?? null,
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageService->upload($request->file('image'), 'categories');
        }

        Category::create($categoryData);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.categories.index');
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
            'name' => $data['name']['en'],
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'description' => $data['description']['en'] ?? null,
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageService->replace(
                $request->file('image'),
                'categories',
                $category->image_url ?? $category->image // try accessor or raw
            );
        }

        $category->update($categoryData);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.categories.index');
    }

    public function destroy(Category $category)
    {
        $this->imageService->delete($category->image_url ?? $category->image);

        $category->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.categories.index');
    }
}
