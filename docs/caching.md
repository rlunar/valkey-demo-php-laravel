# Laravel Caching Workshop: Step-by-Step Performance Optimization

## Overview
This workshop will guide you through implementing various caching strategies in your Laravel application to improve performance and reduce database queries. We'll cover multiple caching layers from basic query caching to advanced Redis implementations.

## Learning Objectives
By the end of this workshop, you'll be able to:
- Implement query result caching
- Cache expensive computations
- Use Redis for session and application caching
- Implement cache tags and invalidation strategies
- Monitor cache performance and hit rates

## Prerequisites
- Laravel application (this project)
- Basic understanding of Laravel Eloquent
- Ubuntu Linux system
- Redis 7.2.4 installed (last BSD3 OSS version)

---

## Step 1: Configure Cache Drivers

### 1.1 Check Current Cache Configuration
First, let's examine your current cache setup:

```bash
# Check current cache driver
php artisan config:show cache.default

# View all cache stores
php artisan config:show cache.stores
```

### 1.2 Install and Configure Redis 7.2.4
Install Redis 7.2.4 (last BSD3 OSS version) on Ubuntu:

```bash
# Update package list
sudo apt update

# Install dependencies
sudo apt install wget lsb-release

# Add Redis GPG key and repository
curl -fsSL https://packages.redis.io/gpg | sudo gpg --dearmor -o /usr/share/keyrings/redis-archive-keyring.gpg
echo "deb [signed-by=/usr/share/keyrings/redis-archive-keyring.gpg] https://packages.redis.io/deb $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/redis.list

# Update package list again
sudo apt update

# Install specific Redis version 7.2.4
sudo apt install redis-server=7:7.2.4-1rl1~$(lsb_release -cs)1

# Hold the package to prevent automatic updates
sudo apt-mark hold redis-server

# Start and enable Redis service
sudo systemctl start redis-server
sudo systemctl enable redis-server

# Verify installation
redis-server --version
```

Update your `.env` file:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 1.3 Install Redis PHP Extension and Dependencies
```bash
# Install PHP Redis extension
sudo apt install php-redis

# Install predis package for Laravel
composer require predis/predis

# Restart PHP-FPM (adjust version as needed)
sudo systemctl restart php8.2-fpm

# Test Redis connection
redis-cli ping
# Should return: PONG
```

```bash
php -d memory_limit=256M artisan db:seed --class=PostSeeder
```

---

## Step 2: Basic Query Caching

### 2.1 Cache Blog Posts List
Let's start by caching the most common query - blog posts listing.

**File: `app/Http/Controllers/BlogController.php`**

```php
use Illuminate\Support\Facades\Cache;

public function index()
{
    // Using Laravel Cacheable Model package
    $posts = Post::cacheFor(3600)
        ->with(['user', 'categories'])
        ->published()
        ->latest()
        ->cachePaginate(10);

    return view('blog.index', compact('posts'));
}
```

### 2.2 Cache Individual Post
Cache individual blog post with its relationships:

```php
public function show($slug)
{
    // Using Laravel Cacheable Model package
    $post = Post::cacheFor(3600, "post.{$slug}")
        ->with(['user', 'categories', 'comments.user'])
        ->where('slug', $slug)
        ->published()
        ->firstOrFail();

    return view('blog.show', compact('post'));
}
```

### 2.3 Cache Popular Posts
Implement caching for the popular posts sidebar:

```php
// In your controller or service class
public function getPopularPosts($limit = 5)
{
    return Post::cacheFor(1800, "popular.posts.{$limit}")
        ->withCount('comments')
        ->orderBy('comments_count', 'desc')
        ->limit($limit)
        ->get();
}
```

---

## Step 3: Model-Level Caching with Laravel Cacheable Model

### 3.1 Install Laravel Cacheable Model Package
Install the `elipZis/laravel-cacheable-model` package for advanced model caching:

```bash
# Install the cacheable model package
composer require elipzis/laravel-cacheable-model

# Publish the configuration (optional)
php artisan vendor:publish --provider="ElipZis\Cacheable\CacheableServiceProvider" --tag="config"
```

### 3.2 Configure the Package
The package configuration will be available at `config/cacheable.php`. Key settings:

```php
// config/cacheable.php
return [
    'enabled' => env('CACHEABLE_ENABLED', true),
    'ttl' => env('CACHEABLE_TTL', 3600), // 1 hour default
    'prefix' => env('CACHEABLE_PREFIX', 'cacheable'),
    'driver' => env('CACHEABLE_DRIVER', null), // Uses default cache driver
    'invalidate_on_update' => true,
    'invalidate_on_delete' => true,
];
```

### 3.3 Apply Cacheable to Models
Add the cacheable trait to your Post model:

**File: `app/Models/Post.php`**

```php
<?php

namespace App\Models;

use ElipZis\Cacheable\Traits\Cacheable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory, Cacheable;

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'published_at', 'user_id'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    // Define cacheable relationships
    protected $cacheableRelations = [
        'user', 'categories', 'comments'
    ];

    // Custom cache TTL for this model (optional)
    protected $cacheTtl = 7200; // 2 hours

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }
}
```

### 3.4 Using the Cacheable Model
The package provides several methods for caching:

```php
// Cache a single model by ID
$post = Post::cacheFind(1);

// Cache a model with relationships
$post = Post::cacheWith(['user', 'categories'])->cacheFind(1);

// Cache query results
$posts = Post::cacheFor(3600)->published()->get();

// Cache paginated results
$posts = Post::cacheFor(1800)->published()->cachePaginate(10);

// Cache specific queries with custom keys
$popularPosts = Post::cacheFor(3600, 'popular_posts')
    ->withCount('comments')
    ->orderBy('comments_count', 'desc')
    ->limit(5)
    ->get();

// Manually invalidate cache
Post::invalidateCache();
Post::invalidateCache(1); // Specific model
```

---

## Step 4: Advanced Caching Strategies

### 4.1 Cache Tags (Redis/Valkey Only)
Use cache tags for better cache management:

```php
// Cache with tags
Cache::tags(['posts', 'blog'])->put('recent.posts', $posts, 3600);

// Invalidate all caches with specific tags
Cache::tags(['posts'])->flush();
```

### 4.2 Cache Expensive Computations
Cache heavy operations like statistics:

**File: `app/Services/BlogStatsService.php`**

```php
<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class BlogStatsService
{
    public function getDashboardStats()
    {
        return Cache::remember('blog.stats.dashboard', 1800, function () {
            return [
                'total_posts' => Post::cacheFor(1800)->count(),
                'published_posts' => Post::cacheFor(1800)->published()->count(),
                'total_users' => User::cacheFor(1800)->count(),
                'posts_this_month' => Post::cacheFor(1800)->whereMonth('created_at', now()->month)->count(),
                'popular_categories' => $this->getPopularCategories(),
            ];
        });
    }

    private function getPopularCategories()
    {
        return Category::cacheFor(3600, 'popular_categories')
            ->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit(5)
            ->get();
    }
}
```

### 4.3 Fragment Caching in Views
Cache expensive view fragments:

**File: `resources/views/partials/sidebar.blade.php`**

```php
@php
    $sidebarData = [
        'recent_posts' => Post::cacheFor(1800, 'sidebar.recent')->latest()->limit(5)->get(),
        'popular_tags' => Tag::cacheFor(1800, 'sidebar.tags')->withCount('posts')->orderBy('posts_count', 'desc')->limit(10)->get(),
        'archive_months' => Post::cacheFor(3600, 'sidebar.archive')
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
    ];
@endphp

<div class="sidebar">
    <!-- Recent Posts -->
    <div class="widget">
        <h3>Recent Posts</h3>
        @foreach($sidebarData['recent_posts'] as $post)
            <div class="recent-post">
                <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
            </div>
        @endforeach
    </div>
</div>
```

---

## Step 5: Cache Invalidation Strategies

### 5.1 Event-Based Cache Invalidation
Create cache invalidation events:

**File: `app/Events/PostUpdated.php`**

```php
<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Foundation\Events\Dispatchable;

class PostUpdated
{
    use Dispatchable;

    public function __construct(public Post $post)
    {
    }
}
```

**File: `app/Listeners/InvalidatePostCache.php`**

```php
<?php

namespace App\Listeners;

use App\Events\PostUpdated;
use Illuminate\Support\Facades\Cache;

class InvalidatePostCache
{
    public function handle(PostUpdated $event)
    {
        $post = $event->post;
        
        // Using Laravel Cacheable Model package
        // This automatically handles model-specific cache invalidation
        $post->invalidateCache();
        
        // Clear related caches manually
        Cache::forget('blog.stats.dashboard');
        Cache::forget('sidebar.recent');
        Cache::forget('sidebar.archive');
        
        // If using tags with Redis/Valkey
        if (config('cache.default') === 'redis') {
            Cache::tags(['posts', 'blog'])->flush();
        }
    }
}
```

### 5.2 Artisan Commands for Cache Management
Create custom cache management commands:

**File: `app/Console/Commands/ClearBlogCache.php`**

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ClearBlogCache extends Command
{
    protected $signature = 'blog:clear-cache {--type=all}';
    protected $description = 'Clear blog-related caches';

    public function handle()
    {
        $type = $this->option('type');

        switch ($type) {
            case 'posts':
                $this->clearPostsCache();
                break;
            case 'stats':
                $this->clearStatsCache();
                break;
            case 'all':
            default:
                $this->clearAllBlogCache();
                break;
        }

        $this->info("Blog cache cleared successfully!");
    }

    private function clearPostsCache()
    {
        // Clear all Post model caches using the package
        Post::invalidateCache();
        
        // Clear specific query caches
        Cache::forget('popular.posts.5');
        Cache::forget('sidebar.recent');
        $this->info('Posts cache cleared.');
    }

    private function clearStatsCache()
    {
        Cache::forget('blog.stats.dashboard');
        Cache::forget('blog.stats.popular_categories');
        $this->info('Stats cache cleared.');
    }

    private function clearAllBlogCache()
    {
        if (config('cache.default') === 'redis') {
            Cache::tags(['posts', 'blog'])->flush();
        } else {
            $this->clearPostsCache();
            $this->clearStatsCache();
        }
        $this->info('All blog cache cleared.');
    }
}
```

---

## Step 6: Testing Your Cache Implementation

### 6.1 Create Cache Tests
**File: `tests/Feature/CacheTest.php`**

```php
<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_posts_are_cached()
    {
        // Create test posts
        Post::factory()->count(5)->create();
        
        // Clear cache
        Cache::flush();
        
        // First request should cache the results
        $posts1 = Post::cacheFor(3600, 'test.posts')->published()->get();
        
        // Check if cache key exists (package handles key generation)
        $this->assertTrue(Cache::has('cacheable:test.posts'));
        
        // Second request should use cache
        $posts2 = Post::cacheFor(3600, 'test.posts')->published()->get();
        
        // Results should be identical
        $this->assertEquals($posts1->count(), $posts2->count());
    }

    public function test_cache_invalidation_on_post_update()
    {
        $post = Post::factory()->create();
        
        // Cache the post using the package
        $cachedPost = Post::cacheFind($post->id);
        
        // Update the post (should auto-invalidate cache)
        $post->update(['title' => 'Updated Title']);
        
        // Cache should be automatically cleared by the package
        // Verify by checking if fresh data is returned
        $freshPost = Post::cacheFind($post->id);
        $this->assertEquals('Updated Title', $freshPost->title);
    }
}
```

### 6.2 Performance Testing Script
Create a simple performance testing script:

**File: `test_cache_performance.php`**

```php
<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Cache;

// Test cache performance
function testCachePerformance()
{
    $iterations = 1000;
    $testData = range(1, 100);
    
    // Test cache write performance
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        Cache::put("test_key_{$i}", $testData, 3600);
    }
    $writeTime = microtime(true) - $start;
    
    // Test cache read performance
    $start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        Cache::get("test_key_{$i}");
    }
    $readTime = microtime(true) - $start;
    
    echo "Cache Performance Results:\n";
    echo "Write Time: " . round($writeTime * 1000, 2) . "ms\n";
    echo "Read Time: " . round($readTime * 1000, 2) . "ms\n";
    echo "Writes per second: " . round($iterations / $writeTime) . "\n";
    echo "Reads per second: " . round($iterations / $readTime) . "\n";
    
    // Cleanup
    for ($i = 0; $i < $iterations; $i++) {
        Cache::forget("test_key_{$i}");
    }
}

testCachePerformance();
```


---

## Workshop Exercises

### Exercise 1: Basic Implementation
1. Implement caching for your blog post listing
2. Add cache invalidation when posts are updated
3. Test the performance difference

### Exercise 2: Advanced Caching
1. Configure the Laravel Cacheable Model package
2. Add cache tags for better management
3. Create cache monitoring

### Exercise 3: Performance Testing
1. Run the performance testing script
2. Compare database query times with and without cache
3. Monitor cache hit rates

---

## Best Practices Summary

1. **Cache Strategically**: Don't cache everything, focus on expensive operations
2. **Set Appropriate TTL**: Balance between performance and data freshness
3. **Implement Proper Invalidation**: Ensure cache is cleared when data changes
4. **Use Cache Tags**: For better cache management (Redis only)
5. **Monitor Performance**: Track cache hit rates and performance metrics
6. **Test Thoroughly**: Always test cache invalidation scenarios
7. **Plan for Failures**: Have fallbacks when cache is unavailable

---

## Troubleshooting

### Common Issues
1. **Cache not clearing**: Check your invalidation logic
2. **Memory issues**: Monitor Redis memory usage
3. **Stale data**: Verify TTL settings and invalidation events
4. **Performance degradation**: Check for cache stampede scenarios

### Debugging Commands
```bash
# Check cache configuration
php artisan config:show cache

# Clear all caches
php artisan cache:clear

# Show cache statistics
php artisan cache:stats

# Clear specific blog caches
php artisan blog:clear-cache

# Ubuntu-specific Redis commands
sudo systemctl status redis-server
sudo systemctl restart redis-server
redis-cli info memory
redis-cli config get maxmemory

# Monitor Redis in real-time
redis-cli monitor

# Check Redis version
redis-server --version
```

### Ubuntu System Optimization

```bash
# Increase system limits for Redis
echo 'vm.overcommit_memory = 1' | sudo tee -a /etc/sysctl.conf
echo 'net.core.somaxconn = 65535' | sudo tee -a /etc/sysctl.conf

# Apply changes
sudo sysctl -p

# Disable Transparent Huge Pages (recommended for Redis)
echo never | sudo tee /sys/kernel/mm/transparent_hugepage/enabled
echo never | sudo tee /sys/kernel/mm/transparent_hugepage/defrag

# Make THP changes persistent
echo 'echo never > /sys/kernel/mm/transparent_hugepage/enabled' | sudo tee -a /etc/rc.local
echo 'echo never > /sys/kernel/mm/transparent_hugepage/defrag' | sudo tee -a /etc/rc.local
```

This workshop provides a comprehensive foundation for implementing caching in your Laravel application. Start with basic query caching and gradually implement more advanced strategies as needed.
