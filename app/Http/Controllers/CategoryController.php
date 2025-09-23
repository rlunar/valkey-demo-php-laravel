<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with post counts.
     */
    public function index()
    {
        $categories = Category::withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('name')
            ->paginate(12);

        return view('categories.index', compact('categories'));
    }

    /**
     * Display posts filtered by category.
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        
        $posts = Post::where('category_id', $category->id)
            ->where('status', 'published')
            ->with(['user', 'category', 'tags'])
            ->latest('published_at')
            ->paginate(10);

        // Get other categories for sidebar
        $otherCategories = Category::where('id', '!=', $category->id)
            ->withCount(['posts' => function ($query) {
                $query->where('status', 'published');
            }])
            ->orderBy('name')
            ->limit(5)
            ->get();

        return view('categories.show', compact('category', 'posts', 'otherCategories'));
    }

    /**
     * Show the form for creating a new category (admin only).
     */
    public function create()
    {
        Gate::authorize('admin');
        
        return view('categories.admin.create');
    }

    /**
     * Store a newly created category (admin only).
     */
    public function store(CategoryRequest $request)
    {
        Gate::authorize('admin');
        
        $category = Category::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing a category (admin only).
     */
    public function edit($id)
    {
        Gate::authorize('admin');
        
        $category = Category::findOrFail($id);
        
        return view('categories.admin.edit', compact('category'));
    }

    /**
     * Update the specified category (admin only).
     */
    public function update(CategoryRequest $request, $id)
    {
        Gate::authorize('admin');
        
        $category = Category::findOrFail($id);
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category (admin only).
     */
    public function destroy($id)
    {
        Gate::authorize('admin');
        
        $category = Category::findOrFail($id);
        
        // Prevent deletion if category has posts
        if ($category->posts()->count() > 0) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cannot delete category with ' . $category->posts()->count() . ' assigned posts.');
        }
        
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Display admin category management page.
     */
    public function adminIndex()
    {
        Gate::authorize('admin');
        
        $categories = Category::withCount('posts')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.admin.index', compact('categories'));
    }
}