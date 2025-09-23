<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the blog homepage with published posts.
     */
    public function index(Request $request): View
    {
        $query = Post::published()
            ->with(['user', 'category', 'tags']);

        // Handle category filtering
        if ($request->filled('category')) {
            $categorySlug = $request->get('category');
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Handle tag filtering
        if ($request->filled('tag')) {
            $tagSlug = $request->get('tag');
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        // Handle multiple tag filtering (comma-separated)
        if ($request->filled('tags')) {
            $tagSlugs = explode(',', $request->get('tags'));
            $tagSlugs = array_filter(array_map('trim', $tagSlugs));
            
            if (!empty($tagSlugs)) {
                $query->whereHas('tags', function ($q) use ($tagSlugs) {
                    $q->whereIn('slug', $tagSlugs);
                }, '>=', count($tagSlugs)); // Posts must have ALL specified tags
            }
        }

        // Order by published date
        $query->orderBy('published_at', 'desc');

        // Paginate results and preserve query parameters
        $posts = $query->paginate(10)->withQueryString();

        // Get categories and tags for filtering sidebar
        $categories = Category::whereHas('posts', function ($query) {
                $query->where('status', 'published');
            })
            ->withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('name')
            ->get();

        $popularTags = Tag::popular(20)->get();

        // Get current filters for display
        $currentCategory = null;
        $currentTag = null;
        $currentTags = collect(); // Initialize as empty collection instead of array

        if ($request->filled('category')) {
            $currentCategory = Category::where('slug', $request->get('category'))->first();
        }

        if ($request->filled('tag')) {
            $currentTag = Tag::where('slug', $request->get('tag'))->first();
        }

        if ($request->filled('tags')) {
            $tagSlugs = explode(',', $request->get('tags'));
            $tagSlugs = array_filter(array_map('trim', $tagSlugs));
            $currentTags = Tag::whereIn('slug', $tagSlugs)->get();
        }

        return view('home', compact(
            'posts', 
            'categories', 
            'popularTags', 
            'currentCategory', 
            'currentTag', 
            'currentTags'
        ));
    }

    /**
     * Display an individual post by slug.
     */
    public function show(string $slug): View
    {
        $post = Post::published()
            ->with(['user', 'category', 'tags'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related posts
        $relatedPosts = $post->getRelatedPosts(5);

        return view('posts.show', compact('post', 'relatedPosts'));
    }
}