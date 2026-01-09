<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Http\Requests\Admin\Slider\StoreSliderRequest;
use App\Http\Requests\Admin\Slider\UpdateSliderRequest;
use App\Traits\HasAdminResponse;
use App\Services\ImageService;

use Illuminate\Http\Request;

class SliderController extends Controller
{
    use HasAdminResponse;

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
        $query = Slider::latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        $sliders = $query->paginate(10)->withQueryString();
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
            $data['image'] = $this->imageService->upload($request->file('image'), 'sliders');
        }

        // Unpack localized title
        if (isset($data['title'])) {
            $data['title_en'] = $data['title']['en'] ?? null;
            $data['title_ar'] = $data['title']['ar'] ?? null;
            $data['title'] = $data['title']['en'] ?? null; // Fallback
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
            $data['image'] = $this->imageService->replace(
                $request->file('image'),
                'sliders',
                $slider->image
            );
        }

        // Unpack localized title
        if (isset($data['title'])) {
            $data['title_en'] = $data['title']['en'] ?? null;
            $data['title_ar'] = $data['title']['ar'] ?? null;
            $data['title'] = $data['title']['en'] ?? null; // Fallback
        }

        $slider->update($data);

        return $this->successResponse('admin.sliders.index', __('admin.messages.updated'));
    }

    /**
     * Remove the specified resource in storage.
     */
    public function destroy(Slider $slider)
    {
        $this->imageService->delete($slider->image);

        $slider->delete();

        return $this->successResponse('admin.sliders.index', __('admin.messages.deleted'));
    }
}
