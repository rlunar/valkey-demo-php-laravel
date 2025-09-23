<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category1;
    private Category $category2;
    private Tag $tag1;
    private Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->category1 = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
        $this->category2 = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
        $this->tag1 = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $this->tag2 = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    }

    public function test_index_displays_published_posts()
    {
        // Create published posts
        $publishedPost1 = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $publishedPost2 = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
        ]);

        // Create draft post (should not be displayed)
        Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee($publishedPost1->title);
        $response->assertSee($publishedPost2->title);
        $response->assertViewHas('posts');
        $response->assertViewHas('categories');
        $response->assertViewHas('popularTags');
    }

    public function test_index_filters_posts_by_category()
    {
        // Create posts in different categories
        $techPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Tech Post',
        ]);

        $programmingPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
            'title' => 'Programming Post',
        ]);

        // Filter by technology category
        $response = $this->get('/?category=technology');

        $response->assertStatus(200);
        $response->assertSee('Tech Post');
        $response->assertDontSee('Programming Post');
        $response->assertViewHas('currentCategory');
    }

    public function test_index_filters_posts_by_tag()
    {
        // Create posts with different tags
        $laravelPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Laravel Post',
        ]);
        $laravelPost->tags()->attach($this->tag1);

        $phpPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
            'title' => 'PHP Post',
        ]);
        $phpPost->tags()->attach($this->tag2);

        // Filter by Laravel tag
        $response = $this->get('/?tag=laravel');

        $response->assertStatus(200);
        $response->assertSee('Laravel Post');
        $response->assertDontSee('PHP Post');
        $response->assertViewHas('currentTag');
    }

    public function test_index_filters_posts_by_multiple_tags()
    {
        // Create post with both tags
        $bothTagsPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Both Tags Post',
        ]);
        $bothTagsPost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create post with only one tag
        $oneTagPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
            'title' => 'One Tag Post',
        ]);
        $oneTagPost->tags()->attach($this->tag1);

        // Filter by both tags (should only show post with both tags)
        $response = $this->get('/?tags=laravel,php');

        $response->assertStatus(200);
        $response->assertSee('Both Tags Post');
        $response->assertDontSee('One Tag Post');
        $response->assertViewHas('currentTags');
    }

    public function test_index_combines_category_and_tag_filters()
    {
        // Create post matching both category and tag
        $matchingPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Matching Post',
        ]);
        $matchingPost->tags()->attach($this->tag1);

        // Create post matching only category
        $categoryOnlyPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Category Only Post',
        ]);

        // Filter by both category and tag
        $response = $this->get('/?category=technology&tag=laravel');

        $response->assertStatus(200);
        $response->assertSee('Matching Post');
        $response->assertDontSee('Category Only Post');
    }

    public function test_index_pagination_preserves_query_parameters()
    {
        // Create enough posts to trigger pagination
        for ($i = 1; $i <= 15; $i++) {
            $post = Post::factory()->create([
                'status' => 'published',
                'published_at' => now()->subDays($i),
                'user_id' => $this->user->id,
                'category_id' => $this->category1->id,
            ]);
            $post->tags()->attach($this->tag1);
        }

        $response = $this->get('/?category=technology&tag=laravel&page=2');

        $response->assertStatus(200);
        // Check that pagination links preserve the query parameters
        $response->assertSee('category=technology');
        $response->assertSee('tag=laravel');
    }

    public function test_show_displays_post_with_related_posts()
    {
        // Create main post
        $mainPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'slug' => 'main-post',
        ]);
        $mainPost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create related post (same category)
        $relatedPost1 = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'title' => 'Related Post 1',
        ]);

        // Create related post (shared tag)
        $relatedPost2 = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDays(3),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
            'title' => 'Related Post 2',
        ]);
        $relatedPost2->tags()->attach($this->tag1);

        $response = $this->get('/post/main-post');

        $response->assertStatus(200);
        $response->assertSee($mainPost->title);
        $response->assertViewHas('post');
        $response->assertViewHas('relatedPosts');
    }

    public function test_show_returns_404_for_nonexistent_post()
    {
        $response = $this->get('/post/nonexistent-slug');

        $response->assertStatus(404);
    }

    public function test_show_returns_404_for_draft_post()
    {
        $draftPost = Post::factory()->create([
            'status' => 'draft',
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
            'slug' => 'draft-post',
        ]);

        $response = $this->get('/post/draft-post');

        $response->assertStatus(404);
    }

    public function test_index_eager_loads_relationships()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);
        $post->tags()->attach($this->tag1);

        // Enable query logging to verify eager loading
        \DB::enableQueryLog();

        $response = $this->get('/');

        $queries = \DB::getQueryLog();
        
        // Should have minimal queries due to eager loading
        // Exact count may vary, but should be much less than N+1 queries
        $this->assertLessThan(10, count($queries));
        
        $response->assertStatus(200);
    }
}