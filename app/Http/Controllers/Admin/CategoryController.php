<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ParentCategory;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            // Verification lives on its own page now, so this form must not
            // write those columns — `$request->has()` on a field the form no
            // longer sends would quietly switch every gate off.
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
            // The id has to be listed, or validate() strips it out of the
            // returned data and every existing field would be regenerated —
            // repointing documents users have already uploaded.
            'verification_fields.*.id' => 'nullable|string|max:100',
            'verification_fields.*.type' => 'required|in:file,text,number',
            'verification_fields.*.label_en' => 'nullable|string|max:255',
            'verification_fields.*.label_ar' => 'nullable|string|max:255',
        ]);

        $fields = $this->normalizeVerificationFields($data['verification_fields'] ?? []);
        $gateIsOn = $request->has('requires_verification') || $request->has('mandatory_service_verification');

        // Turning a gate on with no fields locks the user out for good: the app
        // sends them to a verification screen that has nothing to submit, and
        // VerificationController@upload then falls through to the legacy
        // documents[] path they have no way to reach.
        if ($gateIsOn && $fields === []) {
            return back()->withInput()
                ->withErrors(['verification_fields' => __('admin.categories.verification_fields_required')]);
        }

        $category->update([
            'requires_verification' => $request->has('requires_verification') ? 1 : 0,
            'mandatory_service_verification' => $request->has('mandatory_service_verification') ? 1 : 0,
            'verification_requirements_en' => $data['verification_requirements']['en'] ?? null,
            'verification_requirements_ar' => $data['verification_requirements']['ar'] ?? null,
            'verification_fields' => $fields ?: null,
        ]);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.categories.index', ['parent_category_id' => $category->parent_category_id]);
    }

    /**
     * Cleans the submitted verification fields and owns their ids.
     *
     * The id is not a label: `VerificationController@upload` turns it into the
     * validation rule key and into the multipart field name the app has to post
     * under. Letting an admin type it produced nulls, capitals, hyphens and
     * whole Arabic sentences, so it is derived here instead — from the English
     * label, or a neutral `field_N` when there is none.
     *
     * An id that is already in the right shape is kept exactly as it is, so
     * documents users have uploaded under it keep matching.
     *
     * @param  array<int, array>  $fields
     * @return array<int, array{id: string, type: string, label_en: ?string, label_ar: ?string}>
     */
    protected function normalizeVerificationFields(array $fields): array
    {
        $clean = [];
        $taken = [];

        foreach (array_values($fields) as $index => $field) {
            $labelEn = trim((string) ($field['label_en'] ?? '')) ?: null;
            $labelAr = trim((string) ($field['label_ar'] ?? '')) ?: null;

            // A row with no label at all is an empty row the admin left behind.
            if ($labelEn === null && $labelAr === null) {
                continue;
            }

            $existing = trim((string) ($field['id'] ?? ''));
            $id = preg_match('/^[a-z0-9_]+$/', $existing) === 1
                ? $existing
                : $this->deriveFieldId($labelEn, $index);

            $base = $id;
            $n = 2;
            while (in_array($id, $taken, true)) {
                $id = $base . '_' . $n++;
            }
            $taken[] = $id;

            $clean[] = [
                'id' => $id,
                'type' => in_array($field['type'] ?? 'file', ['file', 'text', 'number'], true)
                    ? $field['type']
                    : 'file',
                'label_en' => $labelEn,
                'label_ar' => $labelAr,
            ];
        }

        return $clean;
    }

    /**
     * Builds the id from the English label, with `field_N` as the last resort.
     *
     * The label is the only thing an admin writes that can describe the field
     * in a machine-safe way, so it is preferred — but a label is free text and
     * has to survive anything typed into it.
     */
    protected function deriveFieldId(?string $labelEn, int $index): string
    {
        $slug = $labelEn === null ? '' : $this->slugFromLabel($labelEn);

        return $slug !== '' ? $slug : 'field_' . ($index + 1);
    }

    /**
     * Turns a label into an id, or returns '' when it cannot produce a sane one.
     *
     * Three cases the obvious implementation gets wrong:
     *
     * - A non-Latin label still transliterates, so "شهادة ممارسة" becomes
     *   "shhad_mmars" — a string that reads as a mistake rather than a name.
     *   Latin scripts are kept ("Café" -> "cafe"); anything past Latin
     *   Extended-B is refused so it falls back to `field_N` instead.
     * - A purely numeric slug becomes an *integer* array key in PHP, which
     *   quietly changes the shape of both the rule map in
     *   VerificationController@upload and the stored document keys.
     * - A long sentence would become a 60+ character multipart field name.
     */
    protected function slugFromLabel(string $label): string
    {
        if (preg_match('/[^\x{0000}-\x{024F}]/u', $label) === 1) {
            return '';
        }

        $slug = Str::slug($label, '_');

        if ($slug === '' || preg_match('/^[a-z0-9_]+$/', $slug) !== 1) {
            return '';
        }

        if (ctype_digit($slug)) {
            $slug = 'field_' . $slug;
        }

        return rtrim(mb_substr($slug, 0, 60), '_');
    }
}
