<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoryController extends Controller
{
    public function index()
    {
        $stories = Story::with('user')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.stories.index', compact('stories'));
    }

    public function destroy($id)
    {
        $story = Story::findOrFail($id);
        
        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        return redirect()->back()->with('success', __('admin.messages.deleted_successfully'));
    }
}
