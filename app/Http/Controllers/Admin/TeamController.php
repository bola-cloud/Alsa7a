<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Team;
use App\Models\Sport;
use App\Services\ImageService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index(Club $club)
    {
        $teams = $club->teams()->with('sport')->get();
        return view('admin.teams.index', compact('club', 'teams'));
    }

    public function create(Club $club)
    {
        $sports = Sport::all();
        return view('admin.teams.create', compact('club', 'sports'));
    }

    public function store(Request $request, Club $club)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'age_group' => 'nullable|string|max:100',
            'image' => 'nullable|image',
        ]);

        $data = $request->only(['name', 'sport_id', 'age_group', 'short_name', 'jersey_color', 'coach', 'founded_year']);
        $data['club_id'] = $club->id;
        $data['active'] = $request->has('active');

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->upload($request->file('image'), 'teams');
        }

        Team::create($data);

        $this->flashSuccess(__('admin.messages.created_successfully'));
        return redirect()->route('admin.clubs.teams.index', $club->id);
    }

    public function show(Club $club, Team $team)
    {
        $team->load(['sport', 'members.category']);
        return view('admin.teams.show', compact('club', 'team'));
    }

    public function edit(Club $club, Team $team)
    {
        $sports = Sport::all();
        return view('admin.teams.edit', compact('club', 'team', 'sports'));
    }

    public function update(Request $request, Club $club, Team $team)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sport_id' => 'required|exists:sports,id',
            'age_group' => 'nullable|string|max:100',
            'image' => 'nullable|image',
        ]);

        $data = $request->only(['name', 'sport_id', 'age_group', 'short_name', 'jersey_color', 'coach', 'founded_year']);
        $data['active'] = $request->has('active');

        if ($request->hasFile('image')) {
            $data['image'] = $this->imageService->replace($request->file('image'), 'teams', $team->image);
        }

        $team->update($data);

        $this->flashSuccess(__('admin.messages.updated_successfully'));
        return redirect()->route('admin.clubs.teams.index', $club->id);
    }

    public function destroy(Club $club, Team $team)
    {
        $this->imageService->delete($team->image);
        $team->delete();
        $this->flashSuccess(__('admin.messages.deleted_successfully'));
        return redirect()->route('admin.clubs.teams.index', $club->id);
    }
}
