<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Sport;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadTrait;

class ClubController extends Controller
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
        $query = Club::with('sports')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sport_id')) {
            $query->whereHas('sports', function ($q) use ($request) {
                $q->where('sports.id', $request->sport_id);
            });
        }

        $clubs = $query->paginate(10)->withQueryString();
        $sports = \App\Models\Sport::all();

        return view('admin.clubs.index', compact('clubs', 'sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sports = Sport::all();
        $leagues = \App\Models\League::all();
        return view('admin.clubs.create', compact('sports', 'leagues'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
            'city' => 'required|string',
            'logo' => 'nullable|image',
            'sports' => 'array',
            'leagues' => 'array',
        ]);

        $data = [
            'name' => $request->name['en'],
            'name_en' => $request->name['en'],
            'name_ar' => $request->name['ar'],
            'description' => $request->description['en'] ?? null,
            'description_en' => $request->description['en'] ?? null,
            'description_ar' => $request->description['ar'] ?? null,
            'city' => $request->city,
            'country' => $request->country ?? 'Jordan',
            'founded_year' => $request->founded_year,
            'website' => $request->website,
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $this->imageService->upload($request->file('logo'), 'clubs/logos');
        }

        if ($request->hasFile('banner')) {
            $data['banner_url'] = $this->imageService->upload($request->file('banner'), 'clubs/banners');
        }

        $club = Club::create($data);

        if ($request->has('sports')) {
            $club->sports()->sync($request->sports);
        }

        if ($request->has('leagues')) {
            $club->leagues()->sync($request->leagues);
        }

        $this->flashSuccess('Club created successfully');
        return redirect()->route('admin.clubs.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club)
    {
        // Fetch roster grouped by category
        // Assuming we rely on User model query as in API
        $members = User::where('club_id', $club->id)->with('category')->get();
        $roster = $members->groupBy(function ($user) {
            return $user->category ? $user->category->name : 'Uncategorized';
        });

        return view('admin.clubs.show', compact('club', 'roster'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Club $club)
    {
        $sports = Sport::all();
        $leagues = \App\Models\League::all();
        return view('admin.clubs.edit', compact('club', 'sports', 'leagues'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club)
    {
        $request->validate([
            'name' => 'required|array',
            'name.en' => 'required|string',
            'name.ar' => 'required|string',
            'description' => 'required|array',
            'description.en' => 'nullable|string',
            'description.ar' => 'nullable|string',
        ]);

        $data = [
            'name' => $request->name['en'],
            'name_en' => $request->name['en'],
            'name_ar' => $request->name['ar'],
            'description' => $request->description['en'] ?? null,
            'description_en' => $request->description['en'] ?? null,
            'description_ar' => $request->description['ar'] ?? null,
            'city' => $request->city,
            'country' => $request->country,
            'website' => $request->website,
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $this->imageService->replace(
                $request->file('logo'),
                'clubs/logos',
                $club->logo_url
            );
        }

        if ($request->hasFile('banner')) {
            $data['banner_url'] = $this->imageService->replace(
                $request->file('banner'),
                'clubs/banners',
                $club->banner_url
            );
        }

        $club->update($data);

        if ($request->has('sports')) {
            $club->sports()->sync($request->sports);
        }

        if ($request->has('leagues')) {
            $club->leagues()->sync($request->leagues);
        }

        $this->flashSuccess('Club updated successfully');
        return redirect()->route('admin.clubs.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club)
    {
        $this->imageService->delete($club->logo_url);
        $this->imageService->delete($club->banner_url);

        $club->delete();
        $this->flashSuccess('Club deleted successfully');
        return redirect()->route('admin.clubs.index');
    }
}
