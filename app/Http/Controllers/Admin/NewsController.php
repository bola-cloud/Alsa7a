<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Sport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Media;

class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $news = News::with('sport')->latest()->paginate(10);
        return view('admin.news.index', compact('news'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sports = Sport::all();
        return view('admin.news.create', compact('sports'));
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
        ]);

        $newsData = [
            'title_en' => $data['title']['en'],
            'title_ar' => $data['title']['ar'],
            'content_en' => $data['content']['en'] ?? null,
            'content_ar' => $data['content']['ar'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $newsData['image'] = $request->file('image')->store('news', 'public');
        }

        $news = News::create($newsData);

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('news/gallery', 'public');
                $news->media()->create([
                    'url' => $path,
                    'type' => 'image',
                    'title' => $file->getClientOriginalName(),
                ]);
            }
        }

        // Handle video (upload takes precedence over URL)
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('news/videos', 'public');
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
        return view('admin.news.edit', compact('news', 'sports'));
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
        ]);

        $newsData = [
            'title_en' => $data['title']['en'],
            'title_ar' => $data['title']['ar'],
            'content_en' => $data['content']['en'] ?? null,
            'content_ar' => $data['content']['ar'] ?? null,
            'sport_id' => $data['sport_id'] ?? null,
        ];

        if ($request->hasFile('image')) {
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $newsData['image'] = $request->file('image')->store('news', 'public');
        }

        $news->update($newsData);

        // Handle deleting marked images
        if ($request->has('deleted_images')) {
            $deletedIds = $request->input('deleted_images');
            $mediaToDelete = $news->media()->whereIn('id', $deletedIds)->get();

            foreach ($mediaToDelete as $media) {
                // Check if it's a local file and delete it
                if (!preg_match('#^https?://#i', $media->url)) {
                    Storage::disk('public')->delete($media->url);
                }
                $media->delete();
            }
        }

        // Handle adding more images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('news/gallery', 'public');
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
                if (!preg_match('#^https?://#i', $oldVideo->url)) {
                    Storage::disk('public')->delete($oldVideo->url);
                }
                $oldVideo->delete();
            }

            if ($request->hasFile('video')) {
                $path = $request->file('video')->store('news/videos', 'public');
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
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }
        $news->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->route('admin.news.index');
    }
}
