<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(6);

        $categories = Category::withCount('publishedPosts')->get();
        $featuredPost = Post::published()->latest('published_at')->first();

        return view('blog.index', compact('posts', 'categories', 'featuredPost'));
    }

    public function show(Post $post)
    {
        if (!$post->published) {
            abort(404);
        }

        $post->load('category', 'approvedComments');
        $relatedPosts = Post::published()
            ->where('category_id', $post->category_id)
            ->where('id', '!=', $post->id)
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    public function category(Category $category)
    {
        $posts = $category->publishedPosts()
            ->with('category')
            ->orderBy('published_at', 'desc')
            ->paginate(6);

        $categories = Category::withCount('publishedPosts')->get();

        return view('blog.category', compact('posts', 'categories', 'category'));
    }

    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255',
            'content' => 'required|max:1000',
        ]);

        $post->comments()->create($request->only('name', 'email', 'content'));

        return back()->with('success', 'Comment submitted successfully! It will be reviewed before publishing.');
    }
}
