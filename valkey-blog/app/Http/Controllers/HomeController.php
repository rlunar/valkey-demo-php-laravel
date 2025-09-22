<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the blog homepage with published posts.
     */
    public function index(): View
    {
        $posts = Post::published()
            ->with('user')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return view('home', compact('posts'));
    }

    /**
     * Display an individual post by slug.
     */
    public function show(string $slug): View
    {
        $post = Post::published()
            ->with('user')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('posts.show', compact('post'));
    }
}