<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $admin;
    private Category $category;
    private Tag $tag1;
    private Tag $tag2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
        $this->category = Category::factory()->create(['name' => 'Technology']);
        $this->tag1 = Tag::factory()->create(['name' => 'Laravel']);
        $this->tag2 = Tag::factory()->create(['name' => 'PHP']);
    }

    public function test_index_displays_posts_with_categories_and_tags()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('posts.index');
        $response->assertViewHas('posts');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($post->title);
        $response->assertSee($this->category->name);
    }

    public function test_index_filters_posts_by_category()
    {
        $category2 = Category::factory()->create(['name' => 'Science']);
        
        $post1 = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Tech Post'
        ]);
        
        $post2 = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category2->id,
            'title' => 'Science Post'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['category' => $this->category->id]));

        $response->assertStatus(200);
        $response->assertSee('Tech Post');
        $response->assertDontSee('Science Post');
    }

    public function test_index_filters_posts_by_tag()
    {
        $tag3 = Tag::factory()->create(['name' => 'JavaScript']);
        
        $post1 = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Laravel Post'
        ]);
        $post1->tags()->attach([$this->tag1->id]);
        
        $post2 = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'JavaScript Post'
        ]);
        $post2->tags()->attach([$tag3->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['tag' => $this->tag1->id]));

        $response->assertStatus(200);
        $response->assertSee('Laravel Post');
        $response->assertDontSee('JavaScript Post');
    }

    public function test_index_filters_posts_by_status()
    {
        $publishedPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'published',
            'title' => 'Published Post'
        ]);
        
        $draftPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'draft',
            'title' => 'Draft Post'
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.index', ['status' => 'published']));

        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    public function test_create_displays_categories_and_tags()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.create'));

        $response->assertStatus(200);
        $response->assertViewIs('posts.create');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($this->category->name);
        $response->assertSee($this->tag1->name);
    }

    public function test_store_creates_post_with_category_and_tags()
    {
        $postData = [
            'title' => 'Test Post with Categories and Tags',
            'content' => 'This is test content for the post.',
            'excerpt' => 'Test excerpt',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => ['laravel', 'php', 'web development']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success', 'Post created successfully.');

        $post = Post::where('title', 'Test Post with Categories and Tags')->first();
        $this->assertNotNull($post);
        $this->assertEquals($this->category->id, $post->category_id);
        $this->assertEquals(3, $post->tags()->count());
        
        // Check that tags were created/assigned
        $this->assertTrue($post->tags()->where('name', 'laravel')->exists());
        $this->assertTrue($post->tags()->where('name', 'php')->exists());
        $this->assertTrue($post->tags()->where('name', 'web development')->exists());
    }

    public function test_store_creates_new_tags_automatically()
    {
        $initialTagCount = Tag::count();
        
        $postData = [
            'title' => 'Test Post with New Tags',
            'content' => 'This is test content.',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => ['new tag 1', 'new tag 2']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));
        
        // Check that new tags were created
        $this->assertEquals($initialTagCount + 2, Tag::count());
        $this->assertTrue(Tag::where('name', 'new tag 1')->exists());
        $this->assertTrue(Tag::where('name', 'new tag 2')->exists());
    }

    public function test_store_requires_category()
    {
        $postData = [
            'title' => 'Test Post without Category',
            'content' => 'This is test content.',
            'status' => 'published',
            // Missing category_id
            'tags' => ['laravel']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertSessionHasErrors(['category_id']);
        $this->assertEquals(0, Post::where('title', 'Test Post without Category')->count());
    }

    public function test_edit_displays_post_with_categories_and_tags()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.posts.edit', $post));

        $response->assertStatus(200);
        $response->assertViewIs('posts.edit');
        $response->assertViewHas('post');
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
        $response->assertSee($post->title);
        $response->assertSee($this->category->name);
    }

    public function test_update_modifies_post_category_and_tags()
    {
        $category2 = Category::factory()->create(['name' => 'Science']);
        
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'title' => 'Original Title'
        ]);
        $post->tags()->attach([$this->tag1->id]);

        $updateData = [
            'title' => 'Updated Title',
            'content' => 'Updated content',
            'status' => 'published',
            'category_id' => $category2->id,
            'tags' => ['php', 'javascript', 'vue']
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updateData);

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success', 'Post updated successfully.');

        $post->refresh();
        $this->assertEquals('Updated Title', $post->title);
        $this->assertEquals($category2->id, $post->category_id);
        $this->assertEquals(3, $post->tags()->count());
        
        // Check that old tag was removed and new tags were added
        $this->assertFalse($post->tags()->where('name', 'Laravel')->exists());
        $this->assertTrue($post->tags()->where('name', 'php')->exists());
        $this->assertTrue($post->tags()->where('name', 'javascript')->exists());
        $this->assertTrue($post->tags()->where('name', 'vue')->exists());
    }

    public function test_update_removes_all_tags_when_none_provided()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $updateData = [
            'title' => $post->title,
            'content' => $post->content,
            'status' => $post->status,
            'category_id' => $this->category->id,
            'tags' => [] // Empty tags array
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updateData);

        $response->assertRedirect(route('admin.posts.index'));
        
        $post->refresh();
        $this->assertEquals(0, $post->tags()->count());
    }

    public function test_update_handles_published_at_correctly()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
            'status' => 'draft',
            'published_at' => null
        ]);

        $updateData = [
            'title' => $post->title,
            'content' => $post->content,
            'status' => 'published', // Change from draft to published
            'category_id' => $this->category->id,
            'tags' => []
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.posts.update', $post), $updateData);

        $response->assertRedirect(route('admin.posts.index'));
        
        $post->refresh();
        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
    }

    public function test_destroy_removes_post_and_tag_associations()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);
        $post->tags()->attach([$this->tag1->id, $this->tag2->id]);

        $postId = $post->id;

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.posts.destroy', $post));

        $response->assertRedirect(route('admin.posts.index'));
        $response->assertSessionHas('success', 'Post deleted successfully.');

        // Check that post is deleted
        $this->assertNull(Post::find($postId));
        
        // Check that tags still exist (they shouldn't be deleted)
        $this->assertNotNull(Tag::find($this->tag1->id));
        $this->assertNotNull(Tag::find($this->tag2->id));
    }

    public function test_unauthenticated_user_cannot_access_admin_routes()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $this->category->id,
        ]);

        // Test index
        $response = $this->get(route('admin.posts.index'));
        $response->assertRedirect(route('login'));

        // Test create
        $response = $this->get(route('admin.posts.create'));
        $response->assertRedirect(route('login'));

        // Test store
        $response = $this->post(route('admin.posts.store'), []);
        $response->assertRedirect(route('login'));

        // Test edit
        $response = $this->get(route('admin.posts.edit', $post));
        $response->assertRedirect(route('login'));

        // Test update
        $response = $this->put(route('admin.posts.update', $post), []);
        $response->assertRedirect(route('login'));

        // Test destroy
        $response = $this->delete(route('admin.posts.destroy', $post));
        $response->assertRedirect(route('login'));
    }

    public function test_tag_synchronization_handles_duplicate_tags()
    {
        $postData = [
            'title' => 'Test Post with Duplicate Tags',
            'content' => 'This is test content.',
            'status' => 'published',
            'category_id' => $this->category->id,
            'tags' => ['laravel', 'Laravel', 'LARAVEL', 'php'] // Duplicates with different cases
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), $postData);

        $response->assertRedirect(route('admin.posts.index'));

        $post = Post::where('title', 'Test Post with Duplicate Tags')->first();
        $this->assertNotNull($post);
        
        // Should only have 2 unique tags (laravel and php)
        $this->assertEquals(2, $post->tags()->count());
        $this->assertTrue($post->tags()->where('name', 'laravel')->exists());
        $this->assertTrue($post->tags()->where('name', 'php')->exists());
    }
}