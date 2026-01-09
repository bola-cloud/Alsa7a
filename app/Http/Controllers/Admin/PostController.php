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
    public function index()
    {
        $posts = Post::with('user')->latest()->paginate(20);
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
