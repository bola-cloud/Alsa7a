<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\UploadTrait;

class ClubController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clubs = Club::with('sports')->orderBy('id', 'desc')->paginate(10);
        return view('admin.clubs.index', compact('clubs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sports = Sport::all();
        return view('admin.clubs.create', compact('sports'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
            'city' => 'required|string',
            'logo' => 'nullable|image',
            'sports' => 'array'
        ]);

        $data = [
            'name' => ['en' => $request->name_en, 'ar' => $request->name_ar],
            'city' => $request->city,
            'country' => $request->country ?? 'Jordan',
            'founded_year' => $request->founded_year,
            'website' => $request->website,
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $data['banner_url'] = $request->file('banner')->store('clubs/banners', 'public');
        }

        $club = Club::create($data);

        if ($request->has('sports')) {
            $club->sports()->sync($request->sports);
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
        return view('admin.clubs.edit', compact('club', 'sports'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Club $club)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_ar' => 'required|string',
        ]);

        $data = [
            'name' => ['en' => $request->name_en, 'ar' => $request->name_ar],
            'city' => $request->city,
            'country' => $request->country,
            'website' => $request->website,
            'is_featured' => $request->has('is_featured'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo_url'] = $request->file('logo')->store('clubs/logos', 'public');
        }

        $club->update($data);

        if ($request->has('sports')) {
            $club->sports()->sync($request->sports);
        }

        $this->flashSuccess('Club updated successfully');
        return redirect()->route('admin.clubs.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Club $club)
    {
        $club->delete();
        $this->flashSuccess('Club deleted successfully');
        return redirect()->route('admin.clubs.index');
    }
}
