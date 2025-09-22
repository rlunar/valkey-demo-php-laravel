# Laravel Blog Application with Bootstrap 5

This guide will help you create a complete Laravel blog application using Bootstrap 5 and Blade views with Eloquent models.

## Project Setup

### 1. Create Laravel Project
```bash
composer create-project laravel/laravel blog-app
cd blog-app
```

### 2. Database Configuration
Update your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=blog_app
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 3. Create Database Tables

#### Create Migrations
```bash
php artisan make:migration create_posts_table
php artisan make:migration create_categories_table
php artisan make:migration create_comments_table
```

#### Posts Migration (`database/migrations/xxxx_create_posts_table.php`)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content');
            $table->string('image')->nullable();
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
```

#### Categories Migration (`database/migrations/xxxx_create_categories_table.php`)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color')->default('#007bff');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
```

#### Comments Migration (`database/migrations/xxxx_create_comments_table.php`)
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('content');
            $table->boolean('approved')->default(false);
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
};
```

### 4. Run Migrations
```bash
php artisan migrate
```

## Models

### 1. Post Model (`app/Models/Post.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'image', 
        'published', 'published_at', 'category_id'
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('approved', true);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (!$post->slug) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('published', true)->whereNotNull('published_at');
    }
}
```

### 2. Category Model (`app/Models/Category.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->published();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
}
```

### 3. Comment Model (`app/Models/Comment.php`)
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'content', 'approved', 'post_id'];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }
}
```

## Controllers

### 1. Blog Controller (`app/Http/Controllers/BlogController.php`)
```php
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
```

## Views

### 1. Layout Template (`resources/views/layouts/blog.blade.php`)
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Laravel Blog')</title>
    <meta name="description" content="@yield('description', 'A modern Laravel blog')">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .blog-header {
            border-bottom: 1px solid #e5e5e5;
        }
        
        .blog-title {
            margin-bottom: 0;
            font-size: 2.25rem;
            font-weight: 400;
        }
        
        .blog-description {
            font-size: 1.1rem;
            color: #6c757d;
        }
        
        .card-blog {
            transition: transform 0.2s ease-in-out;
        }
        
        .card-blog:hover {
            transform: translateY(-2px);
        }
        
        .category-badge {
            font-size: 0.75rem;
        }
        
        .blog-footer {
            padding: 2.5rem 0;
            color: #6c757d;
            text-align: center;
            background-color: #f8f9fa;
            border-top: .05rem solid #e5e5e5;
            margin-top: 3rem;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="blog-header py-3">
        <div class="row flex-nowrap justify-content-between align-items-center">
            <div class="col-4 pt-1">
                <a class="link-secondary" href="#">Subscribe</a>
            </div>
            <div class="col-4 text-center">
                <a class="blog-header-logo text-dark text-decoration-none" href="{{ route('blog.index') }}">
                    <h1 class="blog-title">Laravel Blog</h1>
                </a>
            </div>
            <div class="col-4 d-flex justify-content-end align-items-center">
                <a class="link-secondary" href="#" aria-label="Search">
                    <i class="fas fa-search"></i>
                </a>
                <a class="btn btn-sm btn-outline-secondary ms-2" href="#">Sign up</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <div class="nav-scroller py-1 mb-2">
        <nav class="nav d-flex justify-content-between">
            <a class="p-2 link-secondary" href="{{ route('blog.index') }}">Home</a>
            @foreach($categories ?? [] as $category)
                <a class="p-2 link-secondary" href="{{ route('blog.category', $category) }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    </div>

    <div class="container">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="blog-footer">
        <p>Laravel Blog built with <a href="https://getbootstrap.com/">Bootstrap</a> by <a href="#">@yourname</a>.</p>
        <p>
            <a href="#">Back to top</a>
        </p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
```

### 2. Blog Index (`resources/views/blog/index.blade.php`)
```html
@extends('layouts.blog')

@section('title', 'Laravel Blog - Home')
@section('description', 'Welcome to our Laravel blog featuring the latest posts and insights.')

@section('content')
    @if($featuredPost)
    <!-- Featured Post -->
    <div class="p-4 p-md-5 mb-4 text-white rounded bg-dark">
        <div class="col-md-6 px-0">
            <h1 class="display-4 fst-italic">{{ $featuredPost->title }}</h1>
            <p class="lead my-3">{{ $featuredPost->excerpt }}</p>
            <p class="lead mb-0">
                <a href="{{ route('blog.show', $featuredPost) }}" class="text-white fw-bold">Continue reading...</a>
            </p>
        </div>
    </div>
    @endif

    <div class="row mb-2">
        @foreach($posts->take(2) as $post)
        <div class="col-md-6">
            <div class="row g-0 border rounded overflow-hidden flex-md-row mb-4 shadow-sm h-md-250 position-relative">
                <div class="col p-4 d-flex flex-column position-static">
                    <strong class="d-inline-block mb-2 text-primary">{{ $post->category->name }}</strong>
                    <h3 class="mb-0">{{ $post->title }}</h3>
                    <div class="mb-1 text-muted">{{ $post->published_at->format('M d') }}</div>
                    <p class="card-text mb-auto">{{ $post->excerpt }}</p>
                    <a href="{{ route('blog.show', $post) }}" class="stretched-link">Continue reading</a>
                </div>
                @if($post->image)
                <div class="col-auto d-none d-lg-block">
                    <img src="{{ $post->image }}" width="200" height="250" alt="Post image" class="bd-placeholder-img">
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-md-8">
            <h3 class="pb-4 mb-4 fst-italic border-bottom">Latest Posts</h3>

            @foreach($posts->skip(2) as $post)
            <article class="blog-post">
                <h2 class="blog-post-title">
                    <a href="{{ route('blog.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
                </h2>
                <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} by 
                    <span class="badge category-badge" style="background-color: {{ $post->category->color }}">
                        {{ $post->category->name }}
                    </span>
                </p>
                <p>{{ $post->excerpt }}</p>
                <a href="{{ route('blog.show', $post) }}" class="btn btn-outline-primary">Read more</a>
                <hr>
            </article>
            @endforeach

            <!-- Pagination -->
            <nav class="blog-pagination" aria-label="Pagination">
                {{ $posts->links() }}
            </nav>
        </div>

        <div class="col-md-4">
            <div class="position-sticky" style="top: 2rem;">
                <!-- About -->
                <div class="p-4 mb-3 bg-light rounded">
                    <h4 class="fst-italic">About</h4>
                    <p class="mb-0">Welcome to our Laravel blog! Here you'll find the latest insights, tutorials, and thoughts on web development, Laravel, and more.</p>
                </div>

                <!-- Categories -->
                <div class="p-4">
                    <h4 class="fst-italic">Categories</h4>
                    <ol class="list-unstyled mb-0">
                        @foreach($categories as $category)
                        <li>
                            <a href="{{ route('blog.category', $category) }}" class="d-flex justify-content-between">
                                {{ $category->name }}
                                <span class="badge bg-secondary">{{ $category->published_posts_count }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ol>
                </div>

                <!-- Social -->
                <div class="p-4">
                    <h4 class="fst-italic">Elsewhere</h4>
                    <ol class="list-unstyled">
                        <li><a href="#">GitHub</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Facebook</a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection
```

### 3. Blog Post Detail (`resources/views/blog/show.blade.php`)
```html
@extends('layouts.blog')

@section('title', $post->title . ' - Laravel Blog')
@section('description', $post->excerpt)

@section('content')
<div class="row">
    <div class="col-md-8">
        <article class="blog-post">
            <h2 class="blog-post-title">{{ $post->title }}</h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }} in 
                <a href="{{ route('blog.category', $post->category) }}" class="badge category-badge text-decoration-none" style="background-color: {{ $post->category->color }}">
                    {{ $post->category->name }}
                </a>
            </p>

            @if($post->image)
            <img src="{{ $post->image }}" class="img-fluid mb-4" alt="{{ $post->title }}">
            @endif

            <div class="blog-content">
                {!! nl2br(e($post->content)) !!}
            </div>
        </article>

        <!-- Comments Section -->
        <section class="mt-5">
            <h4>Comments ({{ $post->approvedComments->count() }})</h4>
            
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <!-- Comment Form -->
            <form action="{{ route('blog.comment', $post) }}" method="POST" class="mb-4">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Comment</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" 
                              id="content" name="content" rows="4" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Submit Comment</button>
            </form>

            <!-- Comments List -->
            @foreach($post->approvedComments as $comment)
            <div class="border-bottom pb-3 mb-3">
                <h6>{{ $comment->name }}</h6>
                <small class="text-muted">{{ $comment->created_at->format('F d, Y \a\t g:i A') }}</small>
                <p class="mt-2">{{ $comment->content }}</p>
            </div>
            @endforeach
        </section>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- Related Posts -->
            @if($relatedPosts->count() > 0)
            <div class="p-4 mb-3 bg-light rounded">
                <h4 class="fst-italic">Related Posts</h4>
                @foreach($relatedPosts as $relatedPost)
                <div class="mb-2">
                    <a href="{{ route('blog.show', $relatedPost) }}" class="text-decoration-none">
                        {{ $relatedPost->title }}
                    </a>
                    <small class="d-block text-muted">{{ $relatedPost->published_at->format('M d, Y') }}</small>
                </div>
                @endforeach
            </div>
            @endif

            <!-- Back to blog -->
            <div class="p-4">
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Blog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
```

### 4. Category View (`resources/views/blog/category.blade.php`)
```html
@extends('layouts.blog')

@section('title', $category->name . ' - Laravel Blog')
@section('description', 'Posts in ' . $category->name . ' category')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="p-4 p-md-5 mb-4 text-white rounded" style="background-color: {{ $category->color }}">
            <div class="col-md-6 px-0">
                <h1 class="display-4">{{ $category->name }}</h1>
                <p class="lead">{{ $posts->total() }} {{ Str::plural('post', $posts->total()) }} in this category</p>
            </div>
        </div>

        @forelse($posts as $post)
        <article class="blog-post">
            <h2 class="blog-post-title">
                <a href="{{ route('blog.show', $post) }}" class="text-decoration-none">{{ $post->title }}</a>
            </h2>
            <p class="blog-post-meta">{{ $post->published_at->format('F d, Y') }}</p>
            <p>{{ $post->excerpt }}</p>
            <a href="{{ route('blog.show', $post) }}" class="btn btn-outline-primary">Read more</a>
            <hr>
        </article>
        @empty
        <p>No posts found in this category.</p>
        @endforelse

        <!-- Pagination -->
        <nav class="blog-pagination" aria-label="Pagination">
            {{ $posts->links() }}
        </nav>
    </div>

    <div class="col-md-4">
        <div class="position-sticky" style="top: 2rem;">
            <!-- Categories -->
            <div class="p-4">
                <h4 class="fst-italic">All Categories</h4>
                <ol class="list-unstyled mb-0">
                    @foreach($categories as $cat)
                    <li class="{{ $cat->id === $category->id ? 'fw-bold' : '' }}">
                        <a href="{{ route('blog.category', $cat) }}" class="d-flex justify-content-between">
                            {{ $cat->name }}
                            <span class="badge bg-secondary">{{ $cat->published_posts_count }}</span>
                        </a>
                    </li>
                    @endforeach
                </ol>
            </div>

            <!-- Back to blog -->
            <div class="p-4">
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left"></i> Back to Blog
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
```

## Routes

Add these routes to `routes/web.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;

Route::get('/', [BlogController::class, 'index'])->name('blog.index');
Route::get('/post/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/category/{category}', [BlogController::class, 'category'])->name('blog.category');
Route::post('/post/{post}/comment', [BlogController::class, 'storeComment'])->name('blog.comment');
```

## Seeder (Optional)

Create a seeder to populate sample data:

```bash
php artisan make:seeder BlogSeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Post;

class BlogSeeder extends Seeder
{
    public function run()
    {
        // Create categories
        $categories = [
            ['name' => 'Technology', 'color' => '#007bff'],
            ['name' => 'Laravel', 'color' => '#ff2d20'],
            ['name' => 'PHP', 'color' => '#777bb4'],
            ['name' => 'JavaScript', 'color' => '#f7df1e'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create sample posts
        $posts = [
            [
                'title' => 'Getting Started with Laravel',
                'excerpt' => 'Learn the basics of Laravel framework and build your first application.',
                'content' => 'Laravel is a powerful PHP framework that makes web development enjoyable and creative. In this post, we will explore the fundamental concepts of Laravel and guide you through creating your first application...',
                'published' => true,
                'published_at' => now()->subDays(5),
                'category_id' => 2,
            ],
            [
                'title' => 'Modern JavaScript ES6+ Features',
                'excerpt' => 'Explore the latest JavaScript features that will improve your code quality.',
                'content' => 'JavaScript has evolved significantly over the years. ES6 and beyond have introduced many features that make JavaScript more powerful and easier to work with...',
                'published' => true,
                'published_at' => now()->subDays(3),
                'category_id' => 4,
            ],
            [
                'title' => 'Building RESTful APIs with PHP',
                'excerpt' => 'A comprehensive guide to creating robust RESTful APIs using PHP.',
                'content' => 'REST APIs are essential for modern web applications. In this tutorial, we will cover how to build scalable and secure RESTful APIs using PHP...',
                'published' => true,
                'published_at' => now()->subDays(1),
                'category_id' => 3,
            ],
        ];

        foreach ($posts as $postData) {
            Post::create($postData);
        }
    }
}
```

Run the seeder:
```bash
php artisan db:seed --class=BlogSeeder
```

## Final Steps

1. **Start the development server:**
   ```bash
   php artisan serve
   ```

2. **Visit your blog:**
   Open `http://localhost:8000` in your browser

3. **Customize the design:**
   - Modify the CSS in the layout file
   - Add your own images and content
   - Customize colors and fonts

This Laravel blog application includes:
- ✅ Bootstrap 5 responsive design
- ✅ Eloquent models with relationships
- ✅ Post categories and comments
- ✅ SEO-friendly URLs with slugs
- ✅ Pagination
- ✅ Form validation
- ✅ Clean, maintainable code structure

The application is ready to use and can be easily extended with additional features like user authentication, post search, tags, and more!