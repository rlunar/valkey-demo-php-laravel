<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'slug', 'description', 'color'];
        $category = new Category();
        
        $this->assertEquals($fillable, $category->getFillable());
    }

    /** @test */
    public function it_can_create_a_category_with_basic_attributes()
    {
        $category = Category::create([
            'name' => 'Technology',
            'description' => 'Posts about technology',
            'color' => '#007bff'
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Technology',
            'description' => 'Posts about technology',
            'color' => '#007bff'
        ]);
    }

    /** @test */
    public function it_automatically_generates_slug_from_name()
    {
        $category = Category::create([
            'name' => 'Web Development'
        ]);

        $this->assertEquals('web-development', $category->slug);
    }

    /** @test */
    public function it_allows_manual_slug_setting()
    {
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'tech-posts'
        ]);

        $this->assertEquals('tech-posts', $category->slug);
    }

    /** @test */
    public function it_generates_unique_slugs_when_duplicates_exist()
    {
        // Create first category
        $category1 = Category::create([
            'name' => 'Technology',
            'slug' => 'technology'
        ]);

        // Create second category with same slug attempt
        $category2 = Category::create([
            'name' => 'Tech News',
            'slug' => 'technology'  // This should become technology-1
        ]);

        $this->assertEquals('technology', $category1->slug);
        $this->assertEquals('technology-1', $category2->slug);
    }

    /** @test */
    public function it_handles_multiple_duplicate_slugs()
    {
        // Create multiple categories with same slug attempt
        $category1 = Category::create(['name' => 'News', 'slug' => 'news']);
        $category2 = Category::create(['name' => 'Daily News', 'slug' => 'news']);
        $category3 = Category::create(['name' => 'Breaking News', 'slug' => 'news']);

        $this->assertEquals('news', $category1->slug);
        $this->assertEquals('news-1', $category2->slug);
        $this->assertEquals('news-2', $category3->slug);
    }

    /** @test */
    public function it_preserves_slug_when_name_changes_but_slug_was_manually_set()
    {
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'custom-tech'
        ]);

        $category->update(['name' => 'Tech News']);

        // Slug should remain the same since it was manually set
        $this->assertEquals('custom-tech', $category->fresh()->slug);
    }

    /** @test */
    public function it_generates_slug_when_creating_with_empty_slug()
    {
        $category = new Category();
        $category->name = 'Tech News';
        $category->slug = '';  // Explicitly set to empty
        $category->save();

        $this->assertEquals('tech-news', $category->slug);
    }

    /** @test */
    public function it_preserves_manual_slug_when_updating_other_attributes()
    {
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'custom-tech-slug'
        ]);

        $category->update(['description' => 'Updated description']);

        $this->assertEquals('custom-tech-slug', $category->fresh()->slug);
    }

    /** @test */
    public function it_excludes_current_category_when_checking_slug_uniqueness_during_update()
    {
        $category = Category::create([
            'name' => 'Technology'
        ]);

        // Update the same category - should not create duplicate slug
        $category->update(['description' => 'Updated description']);

        $this->assertEquals('technology', $category->fresh()->slug);
    }

    /** @test */
    public function it_has_many_posts_relationship()
    {
        $category = Category::create(['name' => 'Technology']);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $category->posts());
    }

    /** @test */
    public function it_can_retrieve_associated_posts()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        
        $post1 = Post::create([
            'title' => 'First Tech Post',
            'content' => 'Content 1',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
        
        $post2 = Post::create([
            'title' => 'Second Tech Post',
            'content' => 'Content 2',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $this->assertCount(2, $category->posts);
        $this->assertTrue($category->posts->contains($post1));
        $this->assertTrue($category->posts->contains($post2));
    }

    /** @test */
    public function it_has_with_post_count_scope()
    {
        $user = User::factory()->create();
        $category1 = Category::create(['name' => 'Technology']);
        $category2 = Category::create(['name' => 'Sports']);
        
        // Create posts for category1
        Post::create([
            'title' => 'Tech Post 1',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category1->id
        ]);
        
        Post::create([
            'title' => 'Tech Post 2',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category1->id
        ]);

        // Create one post for category2
        Post::create([
            'title' => 'Sports Post',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category2->id
        ]);

        $categoriesWithCount = Category::withPostCount()->get();
        
        $tech = $categoriesWithCount->where('name', 'Technology')->first();
        $sports = $categoriesWithCount->where('name', 'Sports')->first();
        
        $this->assertEquals(2, $tech->posts_count);
        $this->assertEquals(1, $sports->posts_count);
    }

    /** @test */
    public function it_handles_special_characters_in_slug_generation()
    {
        $category = Category::create([
            'name' => 'C++ & Java Programming!'
        ]);

        $this->assertEquals('c-java-programming', $category->slug);
    }

    /** @test */
    public function it_handles_empty_slug_with_existing_name()
    {
        $category = new Category();
        $category->name = 'Technology';
        $category->slug = '';
        $category->save();

        $this->assertEquals('technology', $category->fresh()->slug);
    }

    /** @test */
    public function it_formats_manual_slug_properly()
    {
        $category = Category::create([
            'name' => 'Technology',
            'slug' => 'Custom Tech Slug!'
        ]);

        $this->assertEquals('custom-tech-slug', $category->slug);
    }
}