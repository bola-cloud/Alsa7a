<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Sport\StoreSportRequest;
use App\Http\Requests\Admin\Sport\UpdateSportRequest;
use App\Models\Sport;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class SportController extends Controller
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
        $query = Sport::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Assuming name_en/name_ar columns exist based on previous patterns
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->active == 'yes' ? 1 : 0);
        }

        $sports = $query->paginate(10)->withQueryString();
        return view('admin.sports.index', compact('sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sports.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSportRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']['en']);

        // Unpack localized fields
        $data['name_en'] = $data['name']['en'];
        $data['name_ar'] = $data['name']['ar'];
        $data['name'] = $data['name']['en'];

        if (isset($data['description'])) {
            $data['description_en'] = $data['description']['en'] ?? null;
            $data['description_ar'] = $data['description']['ar'] ?? null;
            $data['description'] = $data['description']['en'] ?? null;
        }

        if ($request->hasFile('icon')) {
            $data['icon_url'] = $this->imageService->upload($request->file('icon'), 'sports');
        }

        Sport::create($data);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.sports.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sport $sport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sport $sport)
    {
        return view('admin.sports.edit', compact('sport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSportRequest $request, Sport $sport)
    {
        $data = $request->validated();
        if (!isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']['en']);
        }

        // Unpack localized fields
        $data['name_en'] = $data['name']['en'];
        $data['name_ar'] = $data['name']['ar'];
        $data['name'] = $data['name']['en'];

        if (isset($data['description'])) {
            $data['description_en'] = $data['description']['en'] ?? null;
            $data['description_ar'] = $data['description']['ar'] ?? null;
            $data['description'] = $data['description']['en'] ?? null;
        }

        if ($request->hasFile('icon')) {
            $data['icon_url'] = $this->imageService->replace(
                $request->file('icon'),
                'sports',
                $sport->icon_url
            );
        }

        $sport->update($data);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.sports.index');
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(Sport $sport)
    {
        // Check relationships to prevent foreign key constraint violations
        if ($sport->leagues()->exists()) {
            $this->flashError(__('Cannot delete sport because it has associated leagues. Please delete the leagues first.'));
            return redirect()->back();
        }

        try {
            $this->imageService->delete($sport->icon_url);
            $sport->delete();
            $this->flashSuccess(__('admin.messages.deleted'));
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $this->flashError(__('Cannot delete sport because it is referenced by other records (e.g., Clubs, Teams).'));
            } else {
                $this->flashError(__('An error occurred while deleting the sport.'));
            }
        }

        return redirect()->route('admin.sports.index');
    }
}
