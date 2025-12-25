<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Http\Requests\Admin\Sport\StoreSportRequest;
use App\Http\Requests\Admin\Sport\UpdateSportRequest;
use App\Traits\HasAdminResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class SportController extends Controller
{
    use HasAdminResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sports = Sport::latest()->paginate(10);
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

        // Handle Icon Upload
        if ($request->hasFile('icon')) {
            $data['icon_url'] = $request->file('icon')->store('sports', 'public');
        }

        // Generate slug
        $data['slug'] = Str::slug($data['name']);

        // Handle Active Checkbox (default to 0 if unchecked)
        $data['active'] = $request->has('active') ? 1 : 0;

        Sport::create($data);

        return $this->successResponse('admin.sports.index', __('admin.messages.created'));
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

        // Handle Icon Update
        if ($request->hasFile('icon')) {
            // Delete old icon if exists and not external
            if ($sport->icon_url && !preg_match('#^https?://#i', $sport->icon_url)) {
                Storage::disk('public')->delete($sport->getRawOriginal('icon_url') ?? $sport->icon_url);
            }
            $data['icon_url'] = $request->file('icon')->store('sports', 'public');
        }

        // Update slug only if name changes? Or keep it synced.
        $data['slug'] = Str::slug($data['name']);

        $data['active'] = $request->has('active') ? 1 : 0;

        $sport->update($data);

        return $this->successResponse('admin.sports.index', __('admin.messages.updated'));
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(Sport $sport)
    {
        // Delete icon if exists
        if ($sport->icon_url && !preg_match('#^https?://#i', $sport->icon_url)) {
            Storage::disk('public')->delete($sport->getRawOriginal('icon_url') ?? $sport->icon_url);
        }

        $sport->delete();

        return $this->successResponse('admin.sports.index', __('admin.messages.deleted'));
    }
}
