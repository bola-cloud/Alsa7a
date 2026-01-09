<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $posts = $query->paginate(20)->withQueryString();
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if ($post->image) {
            Storage::disk('public')->delete($post->image);
        }
        $post->delete();

        $this->flashSuccess(__('admin.messages.deleted'));
        return redirect()->back();
    }

    public function toggle(Post $post)
    {
        $post->update(['is_hidden' => !$post->is_hidden]);

        $message = $post->is_hidden ? 'Post hidden successfully' : 'Post visible successfully';
        $this->flashSuccess($message);
        return redirect()->back();
    }
}
