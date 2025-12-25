<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Http\Requests\Admin\Slider\StoreSliderRequest;
use App\Http\Requests\Admin\Slider\UpdateSliderRequest;
use App\Traits\HasAdminResponse;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    use HasAdminResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = Slider::latest()->paginate(10);
        return view('admin.sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sliders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSliderRequest $request)
    {
        $data = $request->validated();

        // Handle Image Upload
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        Slider::create($data);

        return $this->successResponse('admin.sliders.index', __('admin.messages.created'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSliderRequest $request, Slider $slider)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if exists and not external
            if ($slider->image && !preg_match('#^https?://#i', $slider->image)) {
                Storage::disk('public')->delete($slider->image);
            }
            $data['image'] = $request->file('image')->store('sliders', 'public');
        }

        $slider->update($data);

        return $this->successResponse('admin.sliders.index', __('admin.messages.updated'));
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(Slider $slider)
    {
        if ($slider->image && !preg_match('#^https?://#i', $slider->image)) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();

        return $this->successResponse('admin.sliders.index', __('admin.messages.deleted'));
    }
}
