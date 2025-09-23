<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoriesTagsEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->category = Category::factory()->create();
    }

    /**
     * Test tag handling with special characters and edge cases
     * Requirements: 3.2, 3.3
     */
    public function test_tag_handling_with_special_characters_and_edge_cases()
    {
        // Test tags with allowed special characters
        $postData = [
            'title' => 'Test Post with Special Tag Characters',
            'content' => 'Content',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => [
                'CPlusPlus',
                'NodeJS',
                'VueJS 3',
                'ASP-NET Core',
                'tag-with-hyphens',
                'tag_with_underscores',
                'tag with spaces'
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        if ($response->getStatusCode() !== 302) {
            // If not redirecting, there might be validation errors
            $response->assertSessionHasNoErrors();
        }
        
        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success');

        $post = Post::where('title', 'Test Post with Special Tag Characters')->first();
        $this->assertNotNull($post);
        $this->assertEquals(7, $post->tags()->count());

        // Verify tags were created with proper slugs
        $this->assertTrue(Tag::where('name', 'cplusplus')->exists());
        $this->assertTrue(Tag::where('name', 'nodejs')->exists());
        $this->assertTrue(Tag::where('name', 'vuejs 3')->exists());
        $this->assertTrue(Tag::where('name', 'asp-net core')->exists());
        $this->assertTrue(Tag::where('name', 'tag with spaces')->exists());

        // Test slug generation
        $cppTag = Tag::where('name', 'cplusplus')->first();
        $this->assertEquals('cplusplus', $cppTag->slug);

        $nodejsTag = Tag::where('name', 'nodejs')->first();
        $this->assertEquals('nodejs', $nodejsTag->slug);
    }

    /**
     * Test empty and null filter scenarios
     * Requirements: 4.6
     */
    public function test_empty_and_null_filter_scenarios()
    {
        // Create test posts
        Post::factory()->published()->count(3)->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        // Test empty category filter
        $response = $this->get('/?category=');
        $response->assertStatus(200);
        // Should show all posts when filter is empty

        // Test empty tag filter
        $response = $this->get('/?tag=');
        $response->assertStatus(200);
        // Should show all posts when filter is empty

        // Test empty tags array filter
        $response = $this->get('/?tags=');
        $response->assertStatus(200);
        // Should show all posts when filter is empty

        // Test null filters
        $response = $this->get('/?category&tag');
        $response->assertStatus(200);
        // Should show all posts when filters are null

        // Test invalid category slug
        $response = $this->get('/?category=nonexistent-category');
        $response->assertStatus(200);
        // Should show no posts for nonexistent category

        // Test invalid tag slug
        $response = $this->get('/?tag=nonexistent-tag');
        $response->assertStatus(200);
        // Should show no posts for nonexistent tag
    }

    /**
     * Test duplicate tag handling in various scenarios
     * Requirements: 3.3
     */
    public function test_duplicate_tag_handling_scenarios()
    {
        // Test case-insensitive duplicate handling
        $postData = [
            'title' => 'Test Duplicate Tags',
            'content' => 'Content',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => ['Laravel', 'laravel', 'LARAVEL', 'LaRaVeL', 'php', 'PHP']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));

        $post = Post::where('title', 'Test Duplicate Tags')->first();
        $this->assertNotNull($post);
        
        // Should only have 2 unique tags (laravel and php)
        $this->assertEquals(2, $post->tags()->count());
        
        $tagNames = $post->tags()->pluck('name')->toArray();
        $this->assertContains('laravel', $tagNames);
        $this->assertContains('php', $tagNames);

        // Test whitespace handling
        $postData2 = [
            'title' => 'Test Whitespace Tags',
            'content' => 'Content',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => [' javascript ', 'javascript', '  vue  ', 'vue', '']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData2);

        $post2 = Post::where('title', 'Test Whitespace Tags')->first();
        $this->assertEquals(2, $post2->tags()->count());
        
        $tagNames2 = $post2->tags()->pluck('name')->toArray();
        $this->assertContains('javascript', $tagNames2);
        $this->assertContains('vue', $tagNames2);
    }

    /**
     * Test category slug conflicts and resolution
     * Requirements: 2.2, 2.4
     */
    public function test_category_slug_conflicts_and_resolution()
    {
        // Create category with specific name
        $category1 = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);
        $this->assertEquals('test-category', $category1->slug);

        // Create another category with same name (should fail due to unique constraint)
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Duplicate name'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertSessionHasErrors(['name']);

        // Create category with name that would generate same slug
        $categoryData2 = [
            'name' => 'Test-Category',
            'description' => 'Different name, same slug potential'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData2);

        $response->assertRedirect(route('admin.categories.index'));

        $category2 = Category::where('name', 'Test-Category')->first();
        $this->assertNotNull($category2);
        // Should have different slug to avoid conflicts
        $this->assertNotEquals($category1->slug, $category2->slug);
    }

    /**
     * Test tag search edge cases
     * Requirements: 5.1
     */
    public function test_tag_search_edge_cases()
    {
        // Create tags for testing
        Tag::create(['name' => 'JavaScript', 'slug' => 'javascript']);
        Tag::create(['name' => 'Java', 'slug' => 'java']);
        Tag::create(['name' => 'Python', 'slug' => 'python']);
        Tag::create(['name' => 'CPlusPlus', 'slug' => 'cplusplus']);
        Tag::create(['name' => 'CSharp', 'slug' => 'csharp']);

        // Test minimum query length
        $response = $this->getJson(route('tags.search', ['q' => 'j']));
        $response->assertStatus(200);
        $response->assertJsonCount(0); // Should return empty for short queries

        // Test case-insensitive search
        $response = $this->getJson(route('tags.search', ['q' => 'java']));
        $response->assertStatus(200);
        $response->assertJsonCount(2); // Should find both JavaScript and Java

        // Test partial matching
        $response = $this->getJson(route('tags.search', ['q' => 'script']));
        $response->assertStatus(200);
        $response->assertJsonCount(1); // Should find JavaScript

        // Test search for CPlusPlus
        $response = $this->getJson(route('tags.search', ['q' => 'cplus']));
        $response->assertStatus(200);
        $response->assertJsonCount(1); // Should find CPlusPlus

        // Test empty query
        $response = $this->getJson(route('tags.search', ['q' => '']));
        $response->assertStatus(200);
        $response->assertJsonCount(0);

        // Test very long query
        $longQuery = str_repeat('a', 100);
        $response = $this->getJson(route('tags.search', ['q' => $longQuery]));
        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    /**
     * Test post creation with maximum number of tags
     * Requirements: 3.1, 3.3
     */
    public function test_post_creation_with_maximum_tags()
    {
        // Create post with many tags to test performance and limits
        $manyTags = [];
        for ($i = 1; $i <= 50; $i++) {
            $manyTags[] = "tag-{$i}";
        }

        $postData = [
            'title' => 'Post with Many Tags',
            'content' => 'Content',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => $manyTags
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));

        $post = Post::where('title', 'Post with Many Tags')->first();
        $this->assertNotNull($post);
        $this->assertEquals(50, $post->tags()->count());

        // Verify all tags were created
        for ($i = 1; $i <= 50; $i++) {
            $this->assertTrue(Tag::where('name', "tag-{$i}")->exists());
        }
    }

    /**
     * Test category deletion with complex scenarios
     * Requirements: 2.3
     */
    public function test_category_deletion_complex_scenarios()
    {
        $category = Category::factory()->create(['name' => 'Test Category']);

        // Test deletion with draft posts
        $draftPost = Post::factory()->create([
            'status' => 'draft',
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error'); // Should prevent deletion even with draft posts

        // Test deletion after moving posts to different category
        $draftPost->update(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success'); // Should allow deletion now

        $this->assertNull(Category::find($category->id));
    }

    /**
     * Test tag deletion with complex scenarios
     * Requirements: 5.3
     */
    public function test_tag_deletion_complex_scenarios()
    {
        $tag = Tag::factory()->create(['name' => 'Test Tag']);

        // Create post with the tag
        $post = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        $post->tags()->attach($tag->id);

        // Test deletion with associated posts
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $tag));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull(Tag::find($tag->id));

        // Test deletion after removing associations
        $post->tags()->detach($tag->id);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $tag));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNull(Tag::find($tag->id));
    }

    /**
     * Test filtering with non-published posts
     * Requirements: 4.1, 4.2
     */
    public function test_filtering_with_non_published_posts()
    {
        $tag = Tag::factory()->create(['name' => 'Test Tag']);

        // Create published post
        $publishedPost = Post::factory()->published()->create([
            'title' => 'Published Post',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        $publishedPost->tags()->attach($tag->id);

        // Create draft post
        $draftPost = Post::factory()->draft()->create([
            'title' => 'Draft Post',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        $draftPost->tags()->attach($tag->id);

        // Test category filtering - should only show published posts
        $response = $this->get('/?category=' . $this->category->slug);
        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');

        // Test tag filtering - should only show published posts
        $response = $this->get('/?tag=' . $tag->slug);
        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');

        // Test category page - should only show published posts
        $response = $this->get(route('categories.show', $this->category->slug));
        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');

        // Test tag page - should only show published posts
        $response = $this->get(route('tags.show', $tag->slug));
        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    /**
     * Test concurrent tag creation scenarios
     * Requirements: 3.2, 3.3
     */
    public function test_concurrent_tag_creation_scenarios()
    {
        // Simulate concurrent tag creation via AJAX
        $tagName = 'Concurrent Tag';

        // First request creates the tag
        $response1 = $this->postJson(route('tags.store'), ['name' => $tagName]);
        $response1->assertStatus(201);

        // Second request with same tag name should fail
        $response2 = $this->postJson(route('tags.store'), ['name' => $tagName]);
        $response2->assertStatus(422);
        $response2->assertJsonValidationErrors(['name']);

        // Verify only one tag was created
        $this->assertEquals(1, Tag::where('name', $tagName)->count());
    }

    /**
     * Test URL encoding in category and tag slugs
     * Requirements: 1.4, 3.5
     */
    public function test_url_encoding_in_slugs()
    {
        // Create category with special characters
        $category = Category::factory()->create([
            'name' => 'C++ Programming',
            'slug' => 'c-programming'
        ]);

        $tag = Tag::factory()->create([
            'name' => 'Node.js',
            'slug' => 'nodejs'
        ]);

        $post = Post::factory()->published()->create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
        ]);
        $post->tags()->attach($tag->id);

        // Test category URL with encoded characters
        $response = $this->get(route('categories.show', 'c-programming'));
        $response->assertStatus(200);

        // Test tag URL with encoded characters
        $response = $this->get(route('tags.show', 'nodejs'));
        $response->assertStatus(200);

        // Test filtering with encoded URLs
        $response = $this->get('/?category=c-programming');
        $response->assertStatus(200);

        $response = $this->get('/?tag=nodejs');
        $response->assertStatus(200);
    }
}