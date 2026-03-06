<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ParentCategory;
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
    public function index(Request $request)
    {
        $query = Category::latest();
        $parentCategory = null;

        if ($request->has('parent_category_id')) {
            $query->where('parent_category_id', $request->parent_category_id);
            $parentCategory = ParentCategory::find($request->parent_category_id);
        }

        $categories = $query->paginate(10);
        return view('admin.categories.index', compact('categories', 'parentCategory'));
    }

    public function create(Request $request)
    {
        $parentCategories = ParentCategory::all();
        $selectedParentId = $request->query('parent_category_id');
        return view('admin.categories.create', compact('parentCategories', 'selectedParentId'));
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
            'parent_category_id' => 'required|exists:parent_categories,id',
            'requires_verification' => 'nullable|boolean',
            'verification_requirements' => 'nullable|array',
            'verification_requirements.en' => 'nullable|string',
            'verification_requirements.ar' => 'nullable|string',
            'verification_fields' => 'nullable|array',
        ]);

        $categoryData = [
            'name' => $data['name']['en'],
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'parent_category_id' => $data['parent_category_id'],
            'description' => $data['description']['en'] ?? null,
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'verification_requirements_en' => $data['verification_requirements']['en'] ?? null,
            'verification_requirements_ar' => $data['verification_requirements']['ar'] ?? null,
            'verification_fields' => $data['verification_fields'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $categoryData['image'] = $this->imageService->upload($request->file('image'), 'categories');
        }

        Category::create($categoryData);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.categories.index', ['parent_category_id' => $data['parent_category_id']]);
    }

    public function show(Category $category)
    {
        //
    }

    public function edit(Category $category)
    {
        if ($category->isProtected()) {
            return redirect()->back()->with('swal_error', 'System categories cannot be edited.');
        }
        $parentCategories = ParentCategory::all();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, Category $category)
    {
        if ($category->isProtected()) {
            return redirect()->back()->with('swal_error', 'System categories cannot be updated.');
        }
        $data = $request->validate([
            'image' => 'nullable|image',
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'is_service_provider' => 'nullable|boolean',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'parent_category_id' => 'required|exists:parent_categories,id',
            'requires_verification' => 'nullable|boolean',
            'verification_requirements' => 'nullable|array',
            'verification_requirements.en' => 'nullable|string',
            'verification_requirements.ar' => 'nullable|string',
            'verification_fields' => 'nullable|array',
        ]);

        $categoryData = [
            'name' => $data['name']['en'],
            'name_en' => $data['name']['en'],
            'name_ar' => $data['name']['ar'],
            'parent_category_id' => $data['parent_category_id'],
            'description' => $data['description']['en'] ?? null,
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'verification_requirements_en' => $data['verification_requirements']['en'] ?? null,
            'verification_requirements_ar' => $data['verification_requirements']['ar'] ?? null,
            'verification_fields' => $data['verification_fields'] ?? null,
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
        return redirect()->route('admin.categories.index', ['parent_category_id' => $category->parent_category_id]);
    }

    public function destroy(Category $category)
    {
        if ($category->isProtected()) {
            return redirect()->back()->with('swal_error', 'System categories cannot be deleted.');
        }
        $parentId = $category->parent_category_id;
        $this->imageService->delete($category->image_url ?? $category->image);

        $category->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.categories.index', ['parent_category_id' => $parentId]);
    }
}
