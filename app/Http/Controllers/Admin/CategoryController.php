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

        $categories = $query->paginate(20);
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
            'is_marketplace' => 'nullable|boolean',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'parent_category_id' => 'required|exists:parent_categories,id',
            'requires_verification' => 'nullable|boolean',
            'mandatory_service_verification' => 'nullable|boolean',
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
            'is_marketplace' => $request->has('is_marketplace') ? 1 : 0,
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'mandatory_service_verification' => $request->has('mandatory_service_verification') ? 1 : 0,
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
        $parentCategories = ParentCategory::all();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * An emptied display-name field must clear the override, not store "".
     */
    protected function nullIfBlank(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    public function update(Request $request, Category $category)
    {
        // Protected categories (e.g. "Club"/"نادي") stay editable — the admin
        // panel needs to toggle their checkboxes, description and image —
        // but their identity (name, parent section) is locked for good: the
        // app assumes this exact category/section pair exists. Enforced here
        // server-side, not just by disabling the inputs in the view.
        $locked = $category->isProtected();

        $rules = [
            'image' => 'nullable|image',
            'is_service_provider' => 'nullable|boolean',
            'is_marketplace' => 'nullable|boolean',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'requires_verification' => 'nullable|boolean',
            'mandatory_service_verification' => 'nullable|boolean',
            'verification_requirements' => 'nullable|array',
            'verification_requirements.en' => 'nullable|string',
            'verification_requirements.ar' => 'nullable|string',
            'verification_fields' => 'nullable|array',
            // Wording shown in the mobile app. Editable even for a locked
            // category, because it is display text — the identity the code
            // matches on is the slug, which nothing here can touch.
            'display_name' => 'nullable|array',
            'display_name.en' => 'nullable|string|max:255',
            'display_name.ar' => 'nullable|string|max:255',
        ];

        if (!$locked) {
            $rules['name'] = 'required|array';
            $rules['name.en'] = 'required|string';
            $rules['name.ar'] = 'required|string';
            $rules['parent_category_id'] = 'required|exists:parent_categories,id';
        }

        $data = $request->validate($rules);

        $categoryData = [
            'description' => $data['description']['en'] ?? null,
            'description_en' => $data['description']['en'] ?? null,
            'description_ar' => $data['description']['ar'] ?? null,
            'is_service_provider' => $request->has('is_service_provider') ? 1 : 0,
            'is_marketplace' => $request->has('is_marketplace') ? 1 : 0,
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'mandatory_service_verification' => $request->has('mandatory_service_verification') ? 1 : 0,
            'verification_requirements_en' => $data['verification_requirements']['en'] ?? null,
            'verification_requirements_ar' => $data['verification_requirements']['ar'] ?? null,
            'verification_fields' => $data['verification_fields'] ?? null,
            // Blank means "no override" — the app then falls back to the name.
            'display_name_en' => $this->nullIfBlank($data['display_name']['en'] ?? null),
            'display_name_ar' => $this->nullIfBlank($data['display_name']['ar'] ?? null),
        ];

        if (!$locked) {
            $categoryData['name'] = $data['name']['en'];
            $categoryData['name_en'] = $data['name']['en'];
            $categoryData['name_ar'] = $data['name']['ar'];
            $categoryData['parent_category_id'] = $data['parent_category_id'];
        }

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

    public function verification(Category $category)
    {
        return view('admin.categories.verification', compact('category'));
    }

    public function updateVerification(Request $request, Category $category)
    {
        $data = $request->validate([
            'requires_verification' => 'nullable|boolean',
            'mandatory_service_verification' => 'nullable|boolean',
            'verification_requirements' => 'nullable|array',
            'verification_requirements.en' => 'nullable|string',
            'verification_requirements.ar' => 'nullable|string',
            'verification_fields' => 'nullable|array',
        ]);

        $category->update([
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'mandatory_service_verification' => $request->has('mandatory_service_verification') ? 1 : 0,
            'verification_requirements_en' => $data['verification_requirements']['en'] ?? null,
            'verification_requirements_ar' => $data['verification_requirements']['ar'] ?? null,
            'verification_fields' => $data['verification_fields'] ?? null,
        ]);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.categories.index', ['parent_category_id' => $category->parent_category_id]);
    }
}
