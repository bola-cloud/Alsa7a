<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\League\StoreLeagueRequest;
use App\Http\Requests\Admin\League\UpdateLeagueRequest;
use App\Models\League;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LeagueController extends Controller
{
    public function index()
    {
        // Debugging white page
        // dd('LeagueController index reached'); 

        $leagues = League::with('sport')->latest()->paginate(10);
        return view('admin.leagues.index', compact('leagues'));
    }

    public function create()
    {
        $sports = Sport::all();
        return view('admin.leagues.create', compact('sports'));
    }

    public function store(StoreLeagueRequest $request)
    {
        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']['en']); // Generate slug from EN name

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('leagues', 'public');
        }

        $league = League::create($data);

        return redirect()->route('admin.leagues.index')->with('success', __('admin.messages.created'));
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

        if ($request->hasFile('image')) {
            if ($league->image && Storage::disk('public')->exists($league->image)) {
                Storage::disk('public')->delete($league->image);
            }
            $data['image'] = $request->file('image')->store('leagues', 'public');
        }

        $league->update($data);

        return redirect()->route('admin.leagues.index')->with('success', __('admin.messages.updated'));
    }

    public function destroy(League $league)
    {
        if ($league->image && Storage::disk('public')->exists($league->image)) {
            Storage::disk('public')->delete($league->image);
        }
        $league->delete();
        return redirect()->route('admin.leagues.index')->with('success', __('admin.messages.deleted'));
    }
}
