<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test categories
        $this->category1 = Category::create([
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Tech posts'
        ]);
        
        $this->category2 = Category::create([
            'name' => 'Science',
            'slug' => 'science',
            'description' => 'Science posts'
        ]);
        
        // Create test tags
        $this->tag1 = Tag::create(['name' => 'PHP', 'slug' => 'php']);
        $this->tag2 = Tag::create(['name' => 'Laravel', 'slug' => 'laravel']);
        $this->tag3 = Tag::create(['name' => 'JavaScript', 'slug' => 'javascript']);
    }

    public function test_post_belongs_to_category()
    {
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($this->category1->id, $post->category->id);
        $this->assertEquals('Technology', $post->category->name);
    }

    public function test_post_belongs_to_many_tags()
    {
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $this->assertCount(2, $post->tags);
        $this->assertTrue($post->tags->contains($this->tag1));
        $this->assertTrue($post->tags->contains($this->tag2));
        $this->assertFalse($post->tags->contains($this->tag3));
    }

    public function test_category_id_is_fillable()
    {
        $post = new Post();
        $fillable = $post->getFillable();
        
        $this->assertContains('category_id', $fillable);
    }

    public function test_get_related_posts_by_category()
    {
        // Create main post
        $mainPost = Post::create([
            'title' => 'Main Post',
            'slug' => 'main-post',
            'content' => 'Main content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create related posts in same category
        $relatedPost1 = Post::create([
            'title' => 'Related Post 1',
            'slug' => 'related-post-1',
            'content' => 'Related content 1',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $relatedPost2 = Post::create([
            'title' => 'Related Post 2',
            'slug' => 'related-post-2',
            'content' => 'Related content 2',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create unrelated post in different category
        $unrelatedPost = Post::create([
            'title' => 'Unrelated Post',
            'slug' => 'unrelated-post',
            'content' => 'Unrelated content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
        ]);

        $relatedByCategory = $mainPost->getRelatedByCategory();

        $this->assertCount(2, $relatedByCategory);
        $this->assertTrue($relatedByCategory->contains($relatedPost1));
        $this->assertTrue($relatedByCategory->contains($relatedPost2));
        $this->assertFalse($relatedByCategory->contains($unrelatedPost));
        $this->assertFalse($relatedByCategory->contains($mainPost));
    }

    public function test_get_related_posts_by_tags()
    {
        // Create main post with tags
        $mainPost = Post::create([
            'title' => 'Main Post',
            'slug' => 'main-post',
            'content' => 'Main content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);
        $mainPost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create related post with shared tags
        $relatedPost = Post::create([
            'title' => 'Related Post',
            'slug' => 'related-post',
            'content' => 'Related content',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
        ]);
        $relatedPost->tags()->attach([$this->tag1->id]);

        // Create unrelated post with different tags
        $unrelatedPost = Post::create([
            'title' => 'Unrelated Post',
            'slug' => 'unrelated-post',
            'content' => 'Unrelated content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
        ]);
        $unrelatedPost->tags()->attach([$this->tag3->id]);

        $relatedByTags = $mainPost->getRelatedByTags();

        $this->assertCount(1, $relatedByTags);
        $this->assertTrue($relatedByTags->contains($relatedPost));
        $this->assertFalse($relatedByTags->contains($unrelatedPost));
        $this->assertFalse($relatedByTags->contains($mainPost));
    }

    public function test_get_related_posts_combined_scoring()
    {
        // Create main post
        $mainPost = Post::create([
            'title' => 'Main Post',
            'slug' => 'main-post',
            'content' => 'Main content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);
        $mainPost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create post with same category and shared tags (highest score)
        $highScorePost = Post::create([
            'title' => 'High Score Post',
            'slug' => 'high-score-post',
            'content' => 'High score content',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);
        $highScorePost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create post with same category only (medium score)
        $mediumScorePost = Post::create([
            'title' => 'Medium Score Post',
            'slug' => 'medium-score-post',
            'content' => 'Medium score content',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create post with shared tags only (lower score)
        $lowScorePost = Post::create([
            'title' => 'Low Score Post',
            'slug' => 'low-score-post',
            'content' => 'Low score content',
            'status' => 'published',
            'published_at' => now()->subDays(3),
            'user_id' => $this->user->id,
            'category_id' => $this->category2->id,
        ]);
        $lowScorePost->tags()->attach([$this->tag1->id]);

        $relatedPosts = $mainPost->getRelatedPosts(3);

        $this->assertCount(3, $relatedPosts);
        
        // Check that posts are ordered by relevance score
        $postTitles = $relatedPosts->pluck('title')->toArray();
        $this->assertEquals('High Score Post', $postTitles[0]); // Highest score (category + tags)
        $this->assertEquals('Medium Score Post', $postTitles[1]); // Medium score (category only)
        $this->assertEquals('Low Score Post', $postTitles[2]); // Lowest score (tags only)
    }

    public function test_get_related_posts_excludes_current_post()
    {
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Test content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $relatedPosts = $post->getRelatedPosts();
        
        $this->assertFalse($relatedPosts->contains($post));
    }

    public function test_get_related_posts_only_includes_published_posts()
    {
        // Create main post
        $mainPost = Post::create([
            'title' => 'Main Post',
            'slug' => 'main-post',
            'content' => 'Main content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create draft post in same category
        $draftPost = Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'Draft content',
            'status' => 'draft',
            'published_at' => null,
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create future post in same category
        $futurePost = Post::create([
            'title' => 'Future Post',
            'slug' => 'future-post',
            'content' => 'Future content',
            'status' => 'published',
            'published_at' => now()->addDay(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $relatedPosts = $mainPost->getRelatedPosts();

        $this->assertFalse($relatedPosts->contains($draftPost));
        $this->assertFalse($relatedPosts->contains($futurePost));
    }

    public function test_get_related_posts_respects_limit()
    {
        // Create main post
        $mainPost = Post::create([
            'title' => 'Main Post',
            'slug' => 'main-post',
            'content' => 'Main content',
            'status' => 'published',
            'published_at' => now(),
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        // Create multiple related posts
        for ($i = 1; $i <= 10; $i++) {
            Post::create([
                'title' => "Related Post {$i}",
                'slug' => "related-post-{$i}",
                'content' => "Related content {$i}",
                'status' => 'published',
                'published_at' => now()->subDays($i),
                'user_id' => $this->user->id,
                'category_id' => $this->category1->id,
            ]);
        }

        $relatedPosts = $mainPost->getRelatedPosts(3);
        
        $this->assertCount(3, $relatedPosts);
    }

    public function test_get_related_posts_returns_empty_for_new_post()
    {
        $post = new Post([
            'title' => 'New Post',
            'content' => 'New content',
            'user_id' => $this->user->id,
            'category_id' => $this->category1->id,
        ]);

        $relatedPosts = $post->getRelatedPosts();
        
        $this->assertTrue($relatedPosts->isEmpty());
    }
}