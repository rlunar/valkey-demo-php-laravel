<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a regular user and admin user for testing
        $this->user = User::factory()->create();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_index_displays_categories_with_post_counts()
    {
        $category1 = Category::factory()->create(['name' => 'Technology']);
        $category2 = Category::factory()->create(['name' => 'Travel']);
        
        // Create posts for categories
        Post::factory()->count(3)->create(['category_id' => $category1->id]);
        Post::factory()->count(2)->create(['category_id' => $category2->id]);

        $response = $this->get(route('categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Technology');
        $response->assertSee('Travel');
    }

    public function test_show_displays_posts_for_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Technology']);
        $post1 = Post::factory()->published()->create([
            'category_id' => $category->id,
            'title' => 'Tech Post 1',
            'user_id' => $user->id
        ]);
        $post2 = Post::factory()->published()->create([
            'category_id' => $category->id,
            'title' => 'Tech Post 2',
            'user_id' => $user->id
        ]);

        $response = $this->get(route('categories.show', $category->slug));

        $response->assertStatus(200);
        $response->assertSee('Tech Post 1');
        $response->assertSee('Tech Post 2');
        $response->assertSee($category->name);
    }

    public function test_admin_index_requires_admin_access()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.index'));

        $response->assertStatus(403);
    }

    public function test_admin_index_displays_categories_for_admin()
    {
        $category = Category::factory()->create(['name' => 'Technology']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Technology');
    }

    public function test_create_requires_admin_access()
    {
        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.create'));

        $response->assertStatus(403);
    }

    public function test_create_displays_form_for_admin()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.create'));

        $response->assertStatus(200);
    }

    public function test_store_requires_admin_access()
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => 'A new category description'
        ];

        $response = $this->actingAs($this->user)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertStatus(403);
    }

    public function test_store_creates_category_for_admin()
    {
        $categoryData = [
            'name' => 'New Category',
            'description' => 'A new category description',
            'color' => '#FF5733'
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.categories.store'), $categoryData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'description' => 'A new category description',
            'color' => '#FF5733'
        ]);
    }

    public function test_edit_requires_admin_access()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->get(route('admin.categories.edit', $category));

        $response->assertStatus(403);
    }

    public function test_edit_displays_form_for_admin()
    {
        $category = Category::factory()->create(['name' => 'Technology']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertSee('Technology');
    }

    public function test_update_requires_admin_access()
    {
        $category = Category::factory()->create();
        $updateData = ['name' => 'Updated Category'];

        $response = $this->actingAs($this->user)
            ->put(route('admin.categories.update', $category), $updateData);

        $response->assertStatus(403);
    }

    public function test_update_modifies_category_for_admin()
    {
        $category = Category::factory()->create(['name' => 'Original Name']);
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.categories.update', $category), $updateData);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'description' => 'Updated description'
        ]);
    }

    public function test_destroy_requires_admin_access()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertStatus(403);
    }

    public function test_destroy_deletes_category_without_posts()
    {
        $category = Category::factory()->create(['name' => 'Empty Category']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);
    }

    public function test_destroy_prevents_deletion_of_category_with_posts()
    {
        $category = Category::factory()->create(['name' => 'Category with Posts']);
        Post::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error');
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id
        ]);
    }
}
