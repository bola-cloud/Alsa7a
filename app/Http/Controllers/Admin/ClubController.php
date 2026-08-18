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
        $query = Club::with(['sports', 'owner'])->orderBy('id', 'desc');

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

        $perPage = $request->integer('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100, 250])) {
            $perPage = 10;
        }

        $clubs = $query->paginate($perPage)->withQueryString();
        $sports = Sport::all();

        return view('admin.clubs.index', compact('clubs', 'sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sports = Sport::all();
        $leagues = \App\Models\League::all();
        
        // By slug, so re-wording the category for users cannot empty this list.
        // The name match stays as a fallback for a row with no slug yet.
        $clubCategory = \App\Models\Category::slug(\App\Models\Category::SLUG_CLUB)->first()
            ?? \App\Models\Category::where('name_en', 'Club')
                ->orWhere('name_ar', 'نادي')
                ->first();
            
        $owners = User::where('category_id', $clubCategory?->id)->get();
        $countries = \App\Models\Country::all();
        return view('admin.clubs.create', compact('sports', 'leagues', 'owners', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|array',
            'name.en'         => 'required|string',
            'name.ar'         => 'required|string',
            'description'     => 'required|array',
            'description.en'  => 'nullable|string',
            'description.ar'  => 'nullable|string',
            'city'            => 'required|string',
            'logo'            => 'nullable|image',
            'sports'          => 'array',
            'leagues'         => 'array',
            'user_id'         => 'nullable|exists:users,id', // Owner is now optional
            'founded_year'    => 'nullable|integer|between:1901,2155',
            'country_id'      => 'nullable|exists:countries,id',
        ]);

        $data = [
            'name'           => $request->name['en'],
            'name_en'        => $request->name['en'],
            'name_ar'        => $request->name['ar'],
            'description'    => $request->description['en'] ?? null,
            'description_en' => $request->description['en'] ?? null,
            'description_ar' => $request->description['ar'] ?? null,
            'city'           => $request->city,
            'country'        => $request->country ?? 'Jordan',
            'country_id'     => $request->country_id,
            'founded_year'   => $request->filled('founded_year') ? $request->founded_year : null,
            'website'        => $request->website,
            'is_featured'    => $request->has('is_featured'),
            'user_id'        => $request->user_id,
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

        $this->flashSuccess(__('admin.messages.created_successfully'));
        return redirect()->route('admin.clubs.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Club $club)
    {
        $club->load(['sports', 'leagues', 'owner', 'teams.members']);

        // Fetch roster grouped by category (consistent with API keys)
        $members = User::where('club_id', $club->id)->with('category')->get();
        $roster = $members->groupBy(function ($user) {
            return $user->category ? ($user->category->name_en ?: $user->category->name) : 'Uncategorized';
        })->sortBy(function ($members, $key) {
            return $key === 'Uncategorized' ? 1 : 0; // Put Uncategorized last
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
        
        // By slug, so re-wording the category for users cannot empty this list.
        // The name match stays as a fallback for a row with no slug yet.
        $clubCategory = \App\Models\Category::slug(\App\Models\Category::SLUG_CLUB)->first()
            ?? \App\Models\Category::where('name_en', 'Club')
                ->orWhere('name_ar', 'نادي')
                ->first();
            
        $owners = User::where('category_id', $clubCategory?->id)->get();
        $countries = \App\Models\Country::all();
        return view('admin.clubs.edit', compact('club', 'sports', 'leagues', 'owners', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club)
    {
        $request->validate([
            'name'            => 'required|array',
            'name.en'         => 'required|string',
            'name.ar'         => 'required|string',
            'description'     => 'required|array',
            'description.en'  => 'nullable|string',
            'description.ar'  => 'nullable|string',
            'user_id'         => 'nullable|exists:users,id', // Owner is now optional
            'founded_year'    => 'nullable|integer|between:1901,2155',
            'country_id'      => 'nullable|exists:countries,id',
        ]);

        $data = [
            'name'           => $request->name['en'],
            'name_en'        => $request->name['en'],
            'name_ar'        => $request->name['ar'],
            'description'    => $request->description['en'] ?? null,
            'description_en' => $request->description['en'] ?? null,
            'description_ar' => $request->description['ar'] ?? null,
            'city'           => $request->city,
            'country'        => $request->country,
            'country_id'     => $request->country_id,
            'founded_year'   => $request->filled('founded_year') ? $request->founded_year : null,
            'website'        => $request->website,
            'is_featured'    => $request->has('is_featured'),
            'user_id'        => $request->user_id,
        ];

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $this->imageService->replace(
                $request->file('logo'),
                'clubs/logos',
                $club->getRawOriginal('logo_url')
            );
        }

        if ($request->hasFile('banner')) {
            $data['banner_url'] = $this->imageService->replace(
                $request->file('banner'),
                'clubs/banners',
                $club->getRawOriginal('banner_url')
            );
        }

        $club->update($data);

        if ($request->has('sports')) {
            $club->sports()->sync($request->sports);
        }

        if ($request->has('leagues')) {
            $club->leagues()->sync($request->leagues);
        }

        $this->flashSuccess(__('admin.messages.updated_successfully'));
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

    /**
     * Bulk Actions
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clubs,id',
            'action' => 'required|in:delete',
        ]);

        $ids = $request->ids;
        $action = $request->action;

        if ($action === 'delete') {
            $clubs = Club::whereIn('id', $ids)->get();
            foreach ($clubs as $club) {
                if ($club->logo_url) {
                    $this->imageService->delete($club->logo_url);
                }
                if ($club->banner_url) {
                    $this->imageService->delete($club->banner_url);
                }
                $club->delete();
            }
            $this->flashSuccess(__('admin.messages.deleted_successfully'));
        }

        return redirect()->back();
    }
}
