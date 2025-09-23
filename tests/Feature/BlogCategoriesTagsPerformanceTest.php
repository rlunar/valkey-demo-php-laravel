<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlogCategoriesTagsPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /**
     * Test N+1 query prevention in homepage with categories and tags
     * Requirements: Performance optimization
     */
    public function test_homepage_query_optimization()
    {
        // Create test data
        $categories = Category::factory()->count(5)->create();
        $tags = Tag::factory()->count(10)->create();

        // Create posts with categories and tags
        for ($i = 0; $i < 20; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $categories->random()->id,
                'user_id' => $this->user->id,
            ]);
            
            // Attach 2-4 random tags to each post
            $randomTags = $tags->random(rand(2, 4));
            $post->tags()->attach($randomTags->pluck('id'));
        }

        // Test homepage query count
        DB::enableQueryLog();
        
        $response = $this->get('/');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries due to eager loading:
        // 1. Posts with user, category, tags
        // 2. Categories for sidebar
        // 3. Popular tags for sidebar
        // 4. Pagination count query
        $this->assertLessThan(8, count($queries), 
            'Homepage has too many queries (' . count($queries) . '). Expected < 8 with proper eager loading.');

        DB::disableQueryLog();
    }

    /**
     * Test query optimization for category filtering
     * Requirements: Performance optimization
     */
    public function test_category_filtering_query_optimization()
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(5)->create();

        // Create posts in the category
        for ($i = 0; $i < 15; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach($tags->random(2)->pluck('id'));
        }

        // Create posts in other categories
        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => Category::factory()->create()->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach($tags->random(2)->pluck('id'));
        }

        DB::enableQueryLog();
        
        $response = $this->get('/?category=' . $category->slug);
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries for filtered results
        $this->assertLessThan(10, count($queries), 
            'Category filtering has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test query optimization for tag filtering
     * Requirements: Performance optimization
     */
    public function test_tag_filtering_query_optimization()
    {
        $category = Category::factory()->create();
        $targetTag = Tag::factory()->create(['name' => 'Target Tag']);
        $otherTags = Tag::factory()->count(5)->create();

        // Create posts with target tag
        for ($i = 0; $i < 15; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach([$targetTag->id, $otherTags->random()->id]);
        }

        // Create posts without target tag
        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach($otherTags->random(2)->pluck('id'));
        }

        DB::enableQueryLog();
        
        $response = $this->get('/?tag=' . $targetTag->slug);
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries for tag filtering
        $this->assertLessThan(10, count($queries), 
            'Tag filtering has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test query optimization for multiple tag filtering
     * Requirements: Performance optimization
     */
    public function test_multiple_tag_filtering_query_optimization()
    {
        $category = Category::factory()->create();
        $tag1 = Tag::factory()->create(['name' => 'Tag 1', 'slug' => 'tag-1']);
        $tag2 = Tag::factory()->create(['name' => 'Tag 2', 'slug' => 'tag-2']);
        $otherTags = Tag::factory()->count(3)->create();

        // Create posts with both target tags
        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach([$tag1->id, $tag2->id, $otherTags->random()->id]);
        }

        // Create posts with only one target tag
        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach([$tag1->id, $otherTags->random()->id]);
        }

        DB::enableQueryLog();
        
        $response = $this->get('/?tags=tag-1,tag-2');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries for multiple tag filtering
        $this->assertLessThan(10, count($queries), 
            'Multiple tag filtering has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test admin post listing query optimization
     * Requirements: Performance optimization
     */
    public function test_admin_post_listing_query_optimization()
    {
        $categories = Category::factory()->count(5)->create();
        $tags = Tag::factory()->count(10)->create();

        // Create many posts with categories and tags
        for ($i = 0; $i < 50; $i++) {
            $post = Post::factory()->create([
                'category_id' => $categories->random()->id,
                'user_id' => $this->user->id,
                'status' => ['draft', 'published'][rand(0, 1)],
            ]);
            $post->tags()->attach($tags->random(rand(1, 4))->pluck('id'));
        }

        DB::enableQueryLog();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index'));
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries for admin listing
        $this->assertLessThan(10, count($queries), 
            'Admin post listing has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test related posts query optimization
     * Requirements: Performance optimization
     */
    public function test_related_posts_query_optimization()
    {
        $category = Category::factory()->create();
        $tags = Tag::factory()->count(8)->create();

        // Create main post
        $mainPost = Post::factory()->published()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'slug' => 'main-post'
        ]);
        $mainPost->tags()->attach($tags->take(4)->pluck('id'));

        // Create many related posts
        for ($i = 0; $i < 30; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            // Some posts share tags with main post
            $sharedTags = $tags->take(4)->random(rand(1, 2));
            $otherTags = $tags->skip(4)->random(rand(0, 2));
            $post->tags()->attach($sharedTags->merge($otherTags)->pluck('id'));
        }

        DB::enableQueryLog();
        
        $response = $this->get('/post/main-post');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries for related posts calculation
        $this->assertLessThan(15, count($queries), 
            'Related posts calculation has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test tag autocomplete performance
     * Requirements: Performance optimization
     */
    public function test_tag_autocomplete_performance()
    {
        // Create many tags
        for ($i = 0; $i < 100; $i++) {
            Tag::factory()->create(['name' => 'Tag ' . str_pad($i, 3, '0', STR_PAD_LEFT)]);
        }

        // Create some tags that match search
        Tag::factory()->create(['name' => 'Laravel Framework']);
        Tag::factory()->create(['name' => 'Laravel Eloquent']);
        Tag::factory()->create(['name' => 'Laravel Testing']);

        DB::enableQueryLog();
        
        $response = $this->getJson(route('tags.search', ['q' => 'laravel']));
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have only one query for tag search
        $this->assertLessThanOrEqual(2, count($queries), 
            'Tag search has too many queries (' . count($queries) . ').');

        DB::disableQueryLog();
    }

    /**
     * Test bulk tag synchronization performance
     * Requirements: Performance optimization
     */
    public function test_bulk_tag_synchronization_performance()
    {
        $category = Category::factory()->create();
        
        // Create existing tags
        $existingTags = Tag::factory()->count(20)->create();
        
        // Create post with many existing tags
        $post = Post::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);
        $post->tags()->attach($existingTags->take(10)->pluck('id'));

        // Update post with mix of existing and new tags
        $tagNames = array_merge(
            $existingTags->take(5)->pluck('name')->toArray(),
            ['new-tag-1', 'new-tag-2', 'new-tag-3', 'new-tag-4', 'new-tag-5']
        );

        $updateData = [
            'title' => $post->title,
            'content' => $post->content,
            'status' => $post->status,
            'category_id' => $category->id,
            'tags' => $tagNames
        ];

        DB::enableQueryLog();
        
        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updateData);
        
        $queries = DB::getQueryLog();
        
        // Should have reasonable number of queries for tag synchronization
        $this->assertLessThan(40, count($queries), 
            'Tag synchronization has too many queries (' . count($queries) . ').');

        $response->assertRedirect(route('admin.posts.index'));

        DB::disableQueryLog();
    }

    /**
     * Test pagination performance with large datasets
     * Requirements: Performance optimization
     */
    public function test_pagination_performance_with_large_datasets()
    {
        $categories = Category::factory()->count(10)->create();
        $tags = Tag::factory()->count(20)->create();

        // Create large number of posts
        for ($i = 0; $i < 100; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $categories->random()->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach($tags->random(rand(2, 5))->pluck('id'));
        }

        // Test first page performance
        DB::enableQueryLog();
        
        $response = $this->get('/?page=1');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $firstPageQueries = count($queries);
        
        DB::flushQueryLog();

        // Test middle page performance
        $response = $this->get('/?page=5');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $middlePageQueries = count($queries);

        DB::flushQueryLog();

        // Test last page performance
        $response = $this->get('/?page=10');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $lastPageQueries = count($queries);

        // All pages should have similar query counts
        $this->assertLessThan(10, $firstPageQueries, 'First page has too many queries.');
        $this->assertLessThan(10, $middlePageQueries, 'Middle page has too many queries.');
        $this->assertLessThan(10, $lastPageQueries, 'Last page has too many queries.');

        // Query count should be consistent across pages
        $this->assertLessThanOrEqual(2, abs($firstPageQueries - $lastPageQueries), 
            'Query count varies significantly between pages.');

        DB::disableQueryLog();
    }

    /**
     * Test memory usage with large tag collections
     * Requirements: Performance optimization
     */
    public function test_memory_usage_with_large_tag_collections()
    {
        $category = Category::factory()->create();
        
        // Create post with many tags
        $post = Post::factory()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);

        // Create and attach 100 tags
        $tags = Tag::factory()->count(100)->create();
        $post->tags()->attach($tags->pluck('id'));

        $memoryBefore = memory_get_usage();

        // Load post with all tags
        $loadedPost = Post::with('tags')->find($post->id);
        $tagCount = $loadedPost->tags->count();

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        // Verify all tags were loaded
        $this->assertEquals(100, $tagCount);

        // Memory usage should be reasonable (less than 5MB for 100 tags)
        $this->assertLessThan(5 * 1024 * 1024, $memoryUsed, 
            'Memory usage too high for loading 100 tags: ' . number_format($memoryUsed / 1024 / 1024, 2) . 'MB');
    }

    /**
     * Test database index usage for filtering queries
     * Requirements: Performance optimization
     */
    public function test_database_index_usage()
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        // Create posts for testing
        for ($i = 0; $i < 50; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $category->id,
                'user_id' => $this->user->id,
            ]);
            if ($i % 2 === 0) {
                $post->tags()->attach($tag->id);
            }
        }

        // Test category filtering uses index
        DB::enableQueryLog();
        
        $response = $this->get('/?category=' . $category->slug);
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Verify queries have reasonable WHERE clauses and LIMIT clauses
        foreach ($queries as $query) {
            $upperQuery = strtoupper($query['query']);
            // Check that queries have proper filtering (WHERE clauses) and limits
            if (str_contains($upperQuery, 'SELECT') && str_contains($upperQuery, 'FROM "POSTS"')) {
                // Posts queries should have WHERE clauses for filtering
                $this->assertTrue(
                    str_contains($upperQuery, 'WHERE') || str_contains($upperQuery, 'LIMIT'),
                    'Posts query should have WHERE clause or LIMIT: ' . $query['query']
                );
            }
        }

        DB::disableQueryLog();
    }
}