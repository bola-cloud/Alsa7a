<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\League\StoreLeagueRequest;
use App\Http\Requests\Admin\League\UpdateLeagueRequest;
use App\Models\League;
use App\Models\Sport;
use App\Services\ImageService;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Http\Request;

class LeagueController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Request $request)
    {
        $query = League::with('sport')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Name is JSON but simpler to search title_en/ar if exists, 
                // but League uses Spatie translatable or similar usually? 
                // Controller store method uses: $data['name_en'] = ...
                // So it has name_en and name_ar columns likely.
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $leagues = $query->paginate(10)->withQueryString();
        $sports = Sport::all();

        return view('admin.leagues.index', compact('leagues', 'sports'));
    }

    public function create()
    {
        $sports = Sport::all();
        return view('admin.leagues.create', compact('sports'));
    }

    public function store(StoreLeagueRequest $request)
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

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'leagues');
        }

        League::create($data);

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.leagues.index');
    }

    public function edit(League $league)
    {
        $sports = Sport::all();
        return view('admin.leagues.edit', compact('league', 'sports'));
    }

    public function update(UpdateLeagueRequest $request, League $league)
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

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace(
                $request->file('image'),
                'leagues',
                $league->image
            );
        }

        $league->update($data);

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.leagues.index');
    }

    public function destroy(League $league)
    {
        $this->imageService->delete($league->image);

        $league->delete();
        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.leagues.index');
    }
}
