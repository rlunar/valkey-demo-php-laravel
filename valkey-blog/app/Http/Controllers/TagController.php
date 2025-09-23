<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of tags with usage counts.
     */
    public function index(): View
    {
        $tags = Tag::withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->orderBy('name')
            ->paginate(20);

        return view('tags.index', compact('tags'));
    }

    /**
     * Display posts filtered by a specific tag.
     */
    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();
        
        $posts = Post::whereHas('tags', function ($query) use ($tag) {
            $query->where('tags.id', $tag->id);
        })
        ->with(['user', 'category', 'tags'])
        ->published()
        ->latest()
        ->paginate(10);

        return view('tags.show', compact('tag', 'posts'));
    }

    /**
     * AJAX search method for tag autocomplete functionality.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'slug']);

        return response()->json($tags);
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        $tag = Tag::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
            'message' => 'Tag created successfully.'
        ], 201);
    }

    /**
     * Remove an unused tag from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        // Check if tag is being used by any posts
        if ($tag->posts()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete tag that is being used by posts.');
        }

        $tag->delete();

        return redirect()->back()->with('success', 'Tag deleted successfully.');
    }

    /**
     * Display admin tag management interface.
     */
    public function adminIndex(): View
    {
        $tags = Tag::withCount('posts')
            ->orderBy('name')
            ->paginate(20);

        return view('tags.admin.index', compact('tags'));
    }

    /**
     * Bulk delete unused tags.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'tag_ids' => 'required|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $tagIds = $request->input('tag_ids', []);
        $deletedCount = 0;
        $errors = [];

        foreach ($tagIds as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag) {
                // Check if tag is being used by any posts
                if ($tag->posts()->count() > 0) {
                    $errors[] = "Tag '{$tag->name}' is being used by posts and cannot be deleted.";
                } else {
                    $tag->delete();
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount > 0) {
            $message = "Successfully deleted {$deletedCount} " . Str::plural('tag', $deletedCount) . ".";
            if (!empty($errors)) {
                $message .= " Some tags could not be deleted: " . implode(' ', $errors);
                return redirect()->back()->with('success', $message);
            }
            return redirect()->back()->with('success', $message);
        } else {
            $errorMessage = !empty($errors) ? implode(' ', $errors) : 'No tags were deleted.';
            return redirect()->back()->with('error', $errorMessage);
        }
    }
}