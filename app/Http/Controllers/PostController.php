<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Http\Requests\PostRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PostController extends Controller
{
    /**
     * Display a listing of all posts for admin.
     */
    public function index(Request $request): View
    {
        $query = Post::with(['user', 'category', 'tags']);

        // Filter by category if provided
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by tag if provided
        if ($request->filled('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tags.id', $request->tag);
            });
        }

        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get categories and tags for filter dropdowns
        $categories = Category::withPostCount()->orderBy('name')->get();
        $tags = Tag::withPostCount()->orderBy('name')->get();

        return view('posts.index', compact('posts', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();

        return view('posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(PostRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Extract tags from validated data before creating post
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Create the post
        $post = Post::create($validated);

        // Handle tag synchronization
        $this->syncTags($post, $tags);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): View
    {
        $categories = Category::orderBy('name')->get();
        $tags = Tag::orderBy('name')->get();
        
        // Load the post's current tags for the form
        $post->load('tags');

        return view('posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post in storage.
     */
    public function update(PostRequest $request, Post $post): RedirectResponse
    {
        $validated = $request->validated();
        
        // Extract tags from validated data before updating post
        $tags = $validated['tags'] ?? [];
        unset($validated['tags']);

        // Handle published_at logic for status changes
        if ($validated['status'] === 'published' && $post->status !== 'published') {
            $validated['published_at'] = now();
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        // Update the post
        $post->update($validated);

        // Handle tag synchronization
        $this->syncTags($post, $tags);

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post): RedirectResponse
    {
        // Ensure user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to delete posts.');
        }

        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Synchronize tags for a post, creating new tags as needed.
     */
    private function syncTags(Post $post, array $tagNames): void
    {
        if (empty($tagNames)) {
            // If no tags provided, detach all existing tags
            $post->tags()->detach();
            return;
        }

        $tagIds = [];
        
        foreach ($tagNames as $tagName) {
            // Find existing tag or create new one
            $tag = Tag::firstOrCreate(
                ['name' => trim($tagName)],
                ['slug' => null] // Let the model generate the slug
            );
            
            $tagIds[] = $tag->id;
        }

        // Sync the tags (this will attach new ones and detach removed ones)
        $post->tags()->sync($tagIds);
    }
}