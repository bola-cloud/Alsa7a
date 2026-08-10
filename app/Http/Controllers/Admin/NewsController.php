<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Sport;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Media;

class NewsController extends Controller
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
        $query = News::with('sport')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_en', 'like', "%{$search}%")
                    ->orWhere('title_ar', 'like', "%{$search}%");
            });
        }

        if ($request->filled('sport_id')) {
            $query->where('sport_id', $request->sport_id);
        }

        $news = $query->paginate(10)->withQueryString();
        $sports = Sport::all(); // For filter dropdown

        return view('admin.news.index', compact('news', 'sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sports = Sport::all();
        $countries = \App\Models\Country::all();
        return view('admin.news.create', compact('sports', 'countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'content' => 'required|array',
            'content.en' => 'nullable|string',
            'content.ar' => 'nullable|string',
            'sport_id' => 'nullable|exists:sports,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:20480',
            'video_url' => 'nullable|url',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        $newsData = [
            'title_en' => $data['title']['en'],
            'title_ar' => $data['title']['ar'],
            'content_en' => $data['content']['en'] ?? null,
            'content_ar' => $data['content']['ar'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
            'country_id' => $data['country_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $newsData['image'] = $this->imageService->upload($request->file('image'), 'news');
        }

        $news = News::create($newsData);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->imageService->upload($file, 'news/gallery');
                $news->media()->create([
                    'url' => $path,
                    'type' => 'image',
                    'title' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Handle video (upload takes precedence over URL)
        if ($request->hasFile('video')) {
            $path = $this->imageService->upload($request->file('video'), 'news/videos');
            $news->media()->create([
                'url' => $path,
                'type' => 'video',
            ]);
        } elseif ($request->filled('video_url')) {
            $news->media()->create([
                'url' => $request->video_url,
                'type' => 'video',
            ]);
        }

        $this->flashSuccess(__('admin.messages.created'));
        return redirect()->route('admin.news.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(News $news)
    {
        return view('admin.news.show', compact('news'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(News $news)
    {
        $sports = Sport::all();
        $countries = \App\Models\Country::all();
        return view('admin.news.edit', compact('news', 'sports', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, News $news)
    {
        $data = $request->validate([
            'title' => 'required|array',
            'title.en' => 'required|string',
            'title.ar' => 'required|string',
            'content' => 'required|array',
            'content.en' => 'nullable|string',
            'content.ar' => 'nullable|string',
            'sport_id' => 'nullable|exists:sports,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'video' => 'nullable|mimetypes:video/avi,video/mpeg,video/quicktime,video/mp4|max:20480',
            'video_url' => 'nullable|url',
            'country_id' => 'nullable|exists:countries,id',
        ]);

        $newsData = [
            'title_en' => $data['title']['en'],
            'title_ar' => $data['title']['ar'],
            'content_en' => $data['content']['en'] ?? null,
            'content_ar' => $data['content']['ar'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
            'country_id' => $data['country_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $newsData['image'] = $this->imageService->replace(
                $request->file('image'),
                'news',
                $news->image
            );
        }

        $news->update($newsData);

        // Handle deleting marked images
        if ($request->has('deleted_images')) {
            $deletedIds = $request->input('deleted_images');
            $mediaToDelete = $news->media()->whereIn('id', $deletedIds)->get();

            foreach ($mediaToDelete as $media) {
                // Check if it's a local file and delete it
                $this->imageService->delete($media->url);
                $media->delete();
            }
        }

        // Handle adding more images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->imageService->upload($file, 'news/gallery');
                $news->media()->create([
                    'url' => $path,
                    'type' => 'image',
                    'title' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Handle video update (replace existing if new one provided)
        if ($request->hasFile('video') || $request->filled('video_url')) {
            // Delete old video if exists
            $oldVideo = $news->media()->where('type', 'video')->first();
            if ($oldVideo) {
                $this->imageService->delete($oldVideo->url);
                $oldVideo->delete();
            }

            if ($request->hasFile('video')) {
                $path = $this->imageService->upload($request->file('video'), 'news/videos');
                $news->media()->create([
                    'url' => $path,
                    'type' => 'video',
                ]);
            } elseif ($request->filled('video_url')) {
                $news->media()->create([
                    'url' => $request->video_url,
                    'type' => 'video',
                ]);
            }
        }

        $this->flashSuccess(__('admin.messages.updated'));
        return redirect()->route('admin.news.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(News $news)
    {
        $this->imageService->delete($news->image);

        // Also delete gallery and videos
        foreach ($news->media as $media) {
            $this->imageService->delete($media->url);
            $media->delete();
        }

        $news->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.news.index');
    }
}
