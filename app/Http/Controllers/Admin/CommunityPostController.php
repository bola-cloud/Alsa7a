<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommunityPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = CommunityPost::with(['user', 'category'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('category', function ($catQuery) use ($search) {
                        $catQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $query->where('community_category_id', $request->category_id);
        }

        $posts = $query->paginate(20)->withQueryString();
        $categories = \App\Models\CommunityCategory::all();

        return view('admin.community_posts.index', compact('posts', 'categories'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = CommunityPost::findOrFail($id);

        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->back();
    }

    /**
     * Toggle visibility.
     */
    public function toggle($id)
    {
        $post = CommunityPost::findOrFail($id);
        $post->update(['is_hidden' => !$post->is_hidden]);

        $message = $post->is_hidden ? __('admin.messages.hidden') : __('admin.messages.visible');
        $this->flashSuccess($message);
        return redirect()->back();
    }
}
