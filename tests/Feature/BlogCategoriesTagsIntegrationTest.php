<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlogCategoriesTagsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Category $category1;
    private Category $category2;
    private Tag $tag1;
    private Tag $tag2;
    private Tag $tag3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        
        $this->category1 = Category::factory()->create(['name' => 'Technology', 'slug' => 'technology']);
        $this->category2 = Category::factory()->create(['name' => 'Programming', 'slug' => 'programming']);
        
        $this->tag1 = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        $this->tag2 = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
        $this->tag3 = Tag::factory()->create(['name' => 'JavaScript', 'slug' => 'javascript']);
    }

    /**
     * Test complete post creation workflow with categories and tags
     * Requirements: 1.1, 3.1, 3.2, 3.3
     */
    public function test_complete_post_creation_workflow_with_categories_and_tags()
    {
        // Step 1: Navigate to post creation page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.create'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($this->category1->name);
        $response->assertSee($this->tag1->name);

        // Step 2: Create post with category and new tags
        $postData = [
            'title' => 'Complete Integration Test Post',
            'content' => 'This is a comprehensive test of the blog system with categories and tags.',
            'excerpt' => 'Integration test excerpt',
            'status' => 'published',
            'category_id' => $this->category1->id,
            'tags' => ['laravel', 'testing', 'integration', 'new-tag']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success');

        // Step 3: Verify post was created correctly
        $post = Post::where('title', 'Complete Integration Test Post')->first();
        $this->assertNotNull($post);
        $this->assertEquals($this->category1->id, $post->category_id);
        $this->assertEquals(4, $post->tags()->count());

        // Step 4: Verify new tags were created
        $this->assertTrue(Tag::where('name', 'testing')->exists());
        $this->assertTrue(Tag::where('name', 'integration')->exists());
        $this->assertTrue(Tag::where('name', 'new-tag')->exists());

        // Step 5: Verify post appears on homepage with category and tags
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Complete Integration Test Post');
        $response->assertSee($this->category1->name);
        $response->assertSee('laravel');
        $response->assertSee('testing');

        // Step 6: Verify post can be viewed individually
        $response = $this->get('/post/' . $post->slug);
        $response->assertStatus(200);
        $response->assertSee($post->title);
        $response->assertSee($this->category1->name);
        $response->assertViewHas('relatedPosts');
    }

    /**
     * Test category filtering workflow from user perspective
     * Requirements: 4.1, 4.3, 4.4
     */
    public function test_category_filtering_workflow_from_user_perspective()
    {
        // Create posts in different categories
        $techPost1 = Post::factory()->published()->create([
            'title' => 'Tech Post 1',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);

        $techPost2 = Post::factory()->published()->create([
            'title' => 'Tech Post 2',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);

        $programmingPost = Post::factory()->published()->create([
            'title' => 'Programming Post',
            'category_id' => $this->category2->id,
            'user_id' => $this->user->id,
        ]);

        // Step 1: Visit homepage and see all posts
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Tech Post 1');
        $response->assertSee('Tech Post 2');
        $response->assertSee('Programming Post');

        // Step 2: Click on category filter
        $response = $this->get('/?category=technology');
        $response->assertStatus(200);
        $response->assertSee('Tech Post 1');
        $response->assertSee('Tech Post 2');
        $response->assertDontSee('Programming Post');
        $response->assertViewHas('currentCategory');

        // Step 3: Visit category page directly
        $response = $this->get(route('categories.show', $this->category1->slug));
        $response->assertStatus(200);
        $response->assertSee('Tech Post 1');
        $response->assertSee('Tech Post 2');
        $response->assertDontSee('Programming Post');
        $response->assertSee($this->category1->name);

        // Step 4: Visit categories index
        $response = $this->get(route('categories.index'));
        $response->assertStatus(200);
        $response->assertSee($this->category1->name);
        $response->assertSee($this->category2->name);
    }

    /**
     * Test tag filtering workflow from user perspective
     * Requirements: 4.2, 4.3, 4.4, 4.5
     */
    public function test_tag_filtering_workflow_from_user_perspective()
    {
        // Create posts with different tag combinations
        $laravelPost = Post::factory()->published()->create([
            'title' => 'Laravel Tutorial',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);
        $laravelPost->tags()->attach([$this->tag1->id, $this->tag2->id]); // Laravel, PHP

        $jsPost = Post::factory()->published()->create([
            'title' => 'JavaScript Guide',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);
        $jsPost->tags()->attach([$this->tag3->id]); // JavaScript

        $phpPost = Post::factory()->published()->create([
            'title' => 'Pure PHP Tutorial',
            'category_id' => $this->category2->id,
            'user_id' => $this->user->id,
        ]);
        $phpPost->tags()->attach([$this->tag2->id]); // PHP

        // Step 1: Filter by single tag
        $response = $this->get('/?tag=laravel');
        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial');
        $response->assertDontSee('JavaScript Guide');
        $response->assertDontSee('Pure PHP Tutorial');

        // Step 2: Filter by multiple tags (posts must have ALL tags)
        $response = $this->get('/?tags=laravel,php');
        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial');
        $response->assertDontSee('JavaScript Guide');
        $response->assertDontSee('Pure PHP Tutorial');

        // Step 3: Visit tag page directly
        $response = $this->get(route('tags.show', $this->tag2->slug));
        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial');
        $response->assertSee('Pure PHP Tutorial');
        $response->assertDontSee('JavaScript Guide');

        // Step 4: Visit tags index
        $response = $this->get(route('tags.index'));
        $response->assertStatus(200);
        $response->assertSee($this->tag1->name);
        $response->assertSee($this->tag2->name);
        $response->assertSee($this->tag3->name);

        // Step 5: Combine category and tag filters
        $response = $this->get('/?category=technology&tag=php');
        $response->assertStatus(200);
        $response->assertSee('Laravel Tutorial');
        $response->assertDontSee('Pure PHP Tutorial'); // Different category
        $response->assertDontSee('JavaScript Guide');
    }

    /**
     * Test admin category management interface
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5
     */
    public function test_admin_category_management_interface()
    {
        // Step 1: Access admin category management
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.admin.index');
        $response->assertSee($this->category1->name);
        $response->assertSee($this->category2->name);

        // Step 2: Create new category
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.admin.create');

        $categoryData = [
            'name' => 'New Test Category',
            'description' => 'A category for testing purposes',
            'color' => '#FF5733'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $newCategory = Category::where('name', 'New Test Category')->first();
        $this->assertNotNull($newCategory);
        $this->assertEquals('new-test-category', $newCategory->slug);

        // Step 3: Edit existing category
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $newCategory));

        $response->assertStatus(200);
        $response->assertViewIs('categories.admin.edit');
        $response->assertSee('New Test Category');

        $updateData = [
            'name' => 'Updated Test Category',
            'description' => 'Updated description',
            'color' => '#33FF57'
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $newCategory), $updateData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $newCategory->refresh();
        $this->assertEquals('Updated Test Category', $newCategory->name);

        // Step 4: Try to delete category without posts
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $newCategory));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertNull(Category::find($newCategory->id));

        // Step 5: Try to delete category with posts (should fail)
        Post::factory()->create(['category_id' => $this->category1->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $this->category1));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull(Category::find($this->category1->id));
    }

    /**
     * Test admin tag management interface
     * Requirements: 5.1, 5.2, 5.3, 5.4, 5.5
     */
    public function test_admin_tag_management_interface()
    {
        // Create posts with tags for testing
        $post1 = Post::factory()->create(['category_id' => $this->category1->id]);
        $post1->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $post2 = Post::factory()->create(['category_id' => $this->category2->id]);
        $post2->tags()->attach([$this->tag2->id]);

        // Step 1: Access admin tag management
        $response = $this->actingAs($this->admin)
            ->get('/admin/tags');

        $response->assertStatus(200);
        $response->assertViewIs('tags.admin.index');
        $response->assertSee($this->tag1->name);
        $response->assertSee($this->tag2->name);

        // Step 2: Test tag search functionality
        $response = $this->getJson(route('tags.search', ['q' => 'lar']));
        $response->assertStatus(200);
        $response->assertJsonFragment(['name' => 'Laravel']);

        // Step 3: Create new tag via AJAX
        $response = $this->postJson(route('tags.store'), ['name' => 'New AJAX Tag']);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertTrue(Tag::where('name', 'New AJAX Tag')->exists());

        // Step 4: Delete unused tag
        $unusedTag = Tag::factory()->create(['name' => 'Unused Tag']);
        
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $unusedTag));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertNull(Tag::find($unusedTag->id));

        // Step 5: Try to delete used tag (should fail)
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $this->tag1));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertNotNull(Tag::find($this->tag1->id));
    }

    /**
     * Test error handling and validation scenarios
     * Requirements: All validation requirements
     */
    public function test_error_handling_and_validation_scenarios()
    {
        // Test post creation without category
        $postData = [
            'title' => 'Post Without Category',
            'content' => 'Content',
            'status' => 'published',
            'tags' => ['test']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertSessionHasErrors(['category_id']);

        // Test post creation with invalid category
        $postData['category_id'] = 99999;
        
        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertSessionHasErrors(['category_id']);

        // Test category creation with duplicate name
        $categoryData = [
            'name' => $this->category1->name,
            'description' => 'Duplicate name'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertSessionHasErrors(['name']);

        // Test category creation with invalid color
        $categoryData = [
            'name' => 'Valid Category',
            'color' => 'invalid-color'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertSessionHasErrors(['color']);

        // Test tag creation with duplicate name
        $response = $this->postJson(route('tags.store'), ['name' => $this->tag1->name]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);

        // Test unauthorized access to admin routes
        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->user)
            ->get('/admin/tags');
        $response->assertStatus(403);

        // Test nonexistent resource access
        $response = $this->get(route('categories.show', 'nonexistent-category'));
        $response->assertStatus(404);

        $response = $this->get(route('tags.show', 'nonexistent-tag'));
        $response->assertStatus(404);

        $response = $this->get('/post/nonexistent-post');
        $response->assertStatus(404);
    }

    /**
     * Test related posts functionality
     * Requirements: 6.1, 6.2, 6.3, 6.4, 6.5
     */
    public function test_related_posts_functionality()
    {
        // Create main post
        $mainPost = Post::factory()->published()->create([
            'title' => 'Main Post',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
            'slug' => 'main-post'
        ]);
        $mainPost->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Create related post (same category)
        $relatedPost1 = Post::factory()->published()->create([
            'title' => 'Related by Category',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);

        // Create related post (shared tags)
        $relatedPost2 = Post::factory()->published()->create([
            'title' => 'Related by Tags',
            'category_id' => $this->category2->id,
            'user_id' => $this->user->id,
        ]);
        $relatedPost2->tags()->attach([$this->tag1->id]);

        // Create unrelated post
        $unrelatedPost = Post::factory()->published()->create([
            'title' => 'Unrelated Post',
            'category_id' => $this->category2->id,
            'user_id' => $this->user->id,
        ]);
        $unrelatedPost->tags()->attach([$this->tag3->id]);

        // Test related posts display
        $response = $this->get('/post/main-post');
        $response->assertStatus(200);
        $response->assertViewHas('relatedPosts');
        
        $relatedPosts = $response->viewData('relatedPosts');
        $this->assertGreaterThan(0, $relatedPosts->count());
        
        // Should include related posts but not unrelated ones
        $relatedTitles = $relatedPosts->pluck('title')->toArray();
        $this->assertContains('Related by Category', $relatedTitles);
        $this->assertContains('Related by Tags', $relatedTitles);
        $this->assertNotContains('Unrelated Post', $relatedTitles);
    }

    /**
     * Test performance with multiple categories, tags, and posts
     * Requirements: Performance validation
     */
    public function test_performance_with_multiple_categories_tags_and_posts()
    {
        // Create multiple categories
        $categories = Category::factory()->count(10)->create();
        
        // Create multiple tags
        $tags = Tag::factory()->count(20)->create();
        
        // Create many posts with random categories and tags
        $posts = collect();
        for ($i = 0; $i < 50; $i++) {
            $post = Post::factory()->published()->create([
                'category_id' => $categories->random()->id,
                'user_id' => $this->user->id,
            ]);
            
            // Attach random tags (1-5 tags per post)
            $randomTags = $tags->random(rand(1, 5));
            $post->tags()->attach($randomTags->pluck('id'));
            
            $posts->push($post);
        }

        // Test homepage performance with eager loading
        DB::enableQueryLog();
        
        $response = $this->get('/');
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        
        // Should have minimal queries due to eager loading
        // With proper eager loading, should be less than 10 queries regardless of data size
        $this->assertLessThan(10, count($queries), 'Too many database queries detected. Check eager loading.');

        // Test category filtering performance
        DB::flushQueryLog();
        
        $randomCategory = $categories->random();
        $response = $this->get('/?category=' . $randomCategory->slug);
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $this->assertLessThan(10, count($queries), 'Category filtering has too many queries.');

        // Test tag filtering performance
        DB::flushQueryLog();
        
        $randomTag = $tags->random();
        $response = $this->get('/?tag=' . $randomTag->slug);
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $this->assertLessThan(10, count($queries), 'Tag filtering has too many queries.');

        // Test admin post listing performance
        DB::flushQueryLog();
        
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index'));
        $response->assertStatus(200);
        
        $queries = DB::getQueryLog();
        $this->assertLessThan(10, count($queries), 'Admin post listing has too many queries.');

        DB::disableQueryLog();
    }

    /**
     * Test complete post editing workflow with category and tag changes
     * Requirements: 1.2, 3.1, 3.3
     */
    public function test_complete_post_editing_workflow()
    {
        // Create initial post
        $post = Post::factory()->create([
            'title' => 'Original Post Title',
            'category_id' => $this->category1->id,
            'user_id' => $this->user->id,
        ]);
        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        // Step 1: Navigate to edit page
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.edit', $post));

        $response->assertStatus(200);
        $response->assertViewHas('post');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($post->title);

        // Step 2: Update post with new category and tags
        $updateData = [
            'title' => 'Updated Post Title',
            'content' => $post->content,
            'status' => 'published',
            'category_id' => $this->category2->id, // Change category
            'tags' => ['javascript', 'frontend', 'new-tag'] // Completely different tags
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updateData);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success');

        // Step 3: Verify changes
        $post->refresh();
        $this->assertEquals('Updated Post Title', $post->title);
        $this->assertEquals($this->category2->id, $post->category_id);
        
        $tagNames = $post->tags()->pluck('name')->toArray();
        $this->assertContains('javascript', $tagNames);
        $this->assertContains('frontend', $tagNames);
        $this->assertContains('new-tag', $tagNames);
        $this->assertNotContains('Laravel', $tagNames);
        $this->assertNotContains('PHP', $tagNames);

        // Step 4: Verify new tags were created
        $this->assertTrue(Tag::where('name', 'frontend')->exists());
        $this->assertTrue(Tag::where('name', 'new-tag')->exists());
    }

    /**
     * Test pagination with filters
     * Requirements: 4.4, 4.6
     */
    public function test_pagination_with_filters()
    {
        // Create enough posts to trigger pagination
        for ($i = 1; $i <= 25; $i++) {
            $post = Post::factory()->published()->create([
                'title' => "Test Post {$i}",
                'category_id' => $this->category1->id,
                'user_id' => $this->user->id,
            ]);
            $post->tags()->attach([$this->tag1->id]);
        }

        // Test pagination on homepage with category filter
        $response = $this->get('/?category=technology&page=2');
        $response->assertStatus(200);
        
        // Check that pagination links preserve filters
        $content = $response->getContent();
        $this->assertStringContainsString('category=technology', $content);

        // Test pagination with tag filter
        $response = $this->get('/?tag=laravel&page=2');
        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('tag=laravel', $content);

        // Test pagination with multiple filters
        $response = $this->get('/?category=technology&tag=laravel&page=2');
        $response->assertStatus(200);
        
        $content = $response->getContent();
        $this->assertStringContainsString('category=technology', $content);
        $this->assertStringContainsString('tag=laravel', $content);
    }
}