<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $admin;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->category = Category::factory()->create();
    }

    public function test_index_displays_tags_with_usage_counts(): void
    {
        // Create tags with different usage counts
        $popularTag = Tag::factory()->create(['name' => 'Popular Tag']);
        $lessPopularTag = Tag::factory()->create(['name' => 'Less Popular Tag']);
        $unusedTag = Tag::factory()->create(['name' => 'Unused Tag']);

        // Create posts and associate with tags
        $post1 = Post::factory()->create(['category_id' => $this->category->id, 'user_id' => $this->user->id]);
        $post2 = Post::factory()->create(['category_id' => $this->category->id, 'user_id' => $this->user->id]);
        $post3 = Post::factory()->create(['category_id' => $this->category->id, 'user_id' => $this->user->id]);

        $popularTag->posts()->attach([$post1->id, $post2->id, $post3->id]);
        $lessPopularTag->posts()->attach([$post1->id]);

        $response = $this->get(route('tags.index'));

        $response->assertStatus(200);
        $response->assertViewIs('tags.index');
        $response->assertViewHas('tags');
        $response->assertSee('Popular Tag');
        $response->assertSee('Less Popular Tag');
        $response->assertSee('Unused Tag');
    }

    public function test_show_displays_posts_filtered_by_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Laravel', 'slug' => 'laravel']);
        
        $post1 = Post::factory()->published()->create([
            'title' => 'Laravel Tutorial',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        
        $post2 = Post::factory()->published()->create([
            'title' => 'PHP Basics',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $post3 = Post::factory()->published()->create([
            'title' => 'Another Laravel Post',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        // Associate posts with tag
        $tag->posts()->attach([$post1->id, $post3->id]);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        $response->assertViewIs('tags.show');
        $response->assertViewHas('tag');
        $response->assertViewHas('posts');
        $response->assertSee('Laravel Tutorial');
        $response->assertSee('Another Laravel Post');
        $response->assertDontSee('PHP Basics');
    }

    public function test_show_returns_404_for_nonexistent_tag(): void
    {
        $response = $this->get(route('tags.show', 'nonexistent-tag'));

        $response->assertStatus(404);
    }

    public function test_search_returns_matching_tags_for_autocomplete(): void
    {
        Tag::factory()->create(['name' => 'Laravel']);
        Tag::factory()->create(['name' => 'PHP']);
        Tag::factory()->create(['name' => 'JavaScript']);
        Tag::factory()->create(['name' => 'Python']);

        $response = $this->getJson(route('tags.search', ['q' => 'la']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Laravel']);
        $response->assertJsonMissing(['name' => 'PHP']);
    }

    public function test_search_returns_empty_array_for_short_query(): void
    {
        Tag::factory()->create(['name' => 'Laravel']);

        $response = $this->getJson(route('tags.search', ['q' => 'l']));

        $response->assertStatus(200);
        $response->assertJsonCount(0);
    }

    public function test_search_limits_results_to_ten(): void
    {
        // Create 15 tags that match the search
        for ($i = 1; $i <= 15; $i++) {
            Tag::factory()->create(['name' => "Test Tag {$i}"]);
        }

        $response = $this->getJson(route('tags.search', ['q' => 'test']));

        $response->assertStatus(200);
        $response->assertJsonCount(10);
    }

    public function test_store_creates_new_tag_successfully(): void
    {
        $tagData = ['name' => 'New Tag'];

        $response = $this->postJson(route('tags.store'), $tagData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Tag created successfully.'
        ]);
        $response->assertJsonStructure([
            'success',
            'tag' => ['id', 'name', 'slug'],
            'message'
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'New Tag',
            'slug' => 'new-tag'
        ]);
    }

    public function test_store_validates_required_name(): void
    {
        $response = $this->postJson(route('tags.store'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_unique_name(): void
    {
        Tag::factory()->create(['name' => 'Existing Tag']);

        $response = $this->postJson(route('tags.store'), ['name' => 'Existing Tag']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_destroy_deletes_unused_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Unused Tag']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $tag));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Tag deleted successfully.');
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_destroy_prevents_deletion_of_used_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'Used Tag']);
        $post = Post::factory()->create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id
        ]);
        $tag->posts()->attach($post->id);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.tags.destroy', $tag));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete tag that is being used by posts.');
        $this->assertDatabaseHas('tags', ['id' => $tag->id]);
    }

    public function test_admin_index_displays_tags_for_management(): void
    {
        Tag::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get('/admin/tags');

        $response->assertStatus(200);
        $response->assertViewIs('tags.admin.index');
        $response->assertViewHas('tags');
    }

    public function test_only_published_posts_shown_in_tag_view(): void
    {
        $tag = Tag::factory()->create(['name' => 'Test Tag', 'slug' => 'test-tag']);
        
        $publishedPost = Post::factory()->published()->create([
            'title' => 'Published Post',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);
        
        $draftPost = Post::factory()->draft()->create([
            'title' => 'Draft Post',
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
        ]);

        $tag->posts()->attach([$publishedPost->id, $draftPost->id]);

        $response = $this->get(route('tags.show', $tag->slug));

        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }
}