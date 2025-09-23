<?php

namespace Tests\Unit;

use App\Models\Tag;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = ['name', 'slug'];
        $tag = new Tag();
        
        $this->assertEquals($fillable, $tag->getFillable());
    }

    /** @test */
    public function it_can_create_a_tag_with_basic_attributes()
    {
        $tag = Tag::create([
            'name' => 'Laravel'
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel'
        ]);
    }

    /** @test */
    public function it_automatically_generates_slug_from_name()
    {
        $tag = Tag::create([
            'name' => 'Web Development'
        ]);

        $this->assertEquals('web-development', $tag->slug);
    }

    /** @test */
    public function it_allows_manual_slug_setting()
    {
        $tag = Tag::create([
            'name' => 'Laravel Framework',
            'slug' => 'laravel'
        ]);

        $this->assertEquals('laravel', $tag->slug);
    }

    /** @test */
    public function it_generates_unique_slugs_when_duplicates_exist()
    {
        // Create first tag
        Tag::create([
            'name' => 'PHP',
            'slug' => 'php'
        ]);

        // Create second tag with same slug attempt
        $tag2 = Tag::create([
            'name' => 'PHP Programming',
            'slug' => 'php'  // This should become php-1
        ]);

        $this->assertEquals('php', Tag::first()->slug);
        $this->assertEquals('php-1', $tag2->slug);
    }

    /** @test */
    public function it_handles_multiple_duplicate_slugs()
    {
        // Create multiple tags with same slug attempt
        $tag1 = Tag::create(['name' => 'JavaScript', 'slug' => 'js']);
        $tag2 = Tag::create(['name' => 'JS Framework', 'slug' => 'js']);
        $tag3 = Tag::create(['name' => 'JS Library', 'slug' => 'js']);

        $this->assertEquals('js', $tag1->slug);
        $this->assertEquals('js-1', $tag2->slug);
        $this->assertEquals('js-2', $tag3->slug);
    }

    /** @test */
    public function it_preserves_slug_when_name_changes_but_slug_was_manually_set()
    {
        $tag = Tag::create([
            'name' => 'Laravel',
            'slug' => 'laravel-framework'
        ]);

        $tag->update(['name' => 'Laravel PHP']);

        // Slug should remain the same since it was manually set
        $this->assertEquals('laravel-framework', $tag->fresh()->slug);
    }

    /** @test */
    public function it_generates_slug_when_creating_with_empty_slug()
    {
        $tag = new Tag();
        $tag->name = 'Vue.js';
        $tag->slug = '';  // Explicitly set to empty
        $tag->save();

        $this->assertEquals('vuejs', $tag->slug);
    }

    /** @test */
    public function it_excludes_current_tag_when_checking_slug_uniqueness_during_update()
    {
        $tag = Tag::create([
            'name' => 'React'
        ]);

        // Update the same tag - should not create duplicate slug
        $tag->update(['name' => 'React Framework']);

        $this->assertEquals('react', $tag->fresh()->slug);
    }

    /** @test */
    public function it_has_belongs_to_many_posts_relationship()
    {
        $tag = Tag::create(['name' => 'Laravel']);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, $tag->posts());
    }

    /** @test */
    public function it_can_retrieve_associated_posts()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        $tag = Tag::create(['name' => 'Laravel']);
        
        $post1 = Post::create([
            'title' => 'First Laravel Post',
            'content' => 'Content 1',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
        
        $post2 = Post::create([
            'title' => 'Second Laravel Post',
            'content' => 'Content 2',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        // Attach posts to tag
        $tag->posts()->attach([$post1->id, $post2->id]);

        $this->assertCount(2, $tag->posts);
        $this->assertTrue($tag->posts->contains($post1));
        $this->assertTrue($tag->posts->contains($post2));
    }

    /** @test */
    public function it_has_with_post_count_scope()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        $tag1 = Tag::create(['name' => 'Laravel']);
        $tag2 = Tag::create(['name' => 'PHP']);
        
        // Create posts
        $post1 = Post::create([
            'title' => 'Laravel Post 1',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
        
        $post2 = Post::create([
            'title' => 'Laravel Post 2',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $post3 = Post::create([
            'title' => 'PHP Post',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        // Attach posts to tags
        $tag1->posts()->attach([$post1->id, $post2->id]);
        $tag2->posts()->attach([$post3->id]);

        $tagsWithCount = Tag::withPostCount()->get();
        
        $laravel = $tagsWithCount->where('name', 'Laravel')->first();
        $php = $tagsWithCount->where('name', 'PHP')->first();
        
        $this->assertEquals(2, $laravel->posts_count);
        $this->assertEquals(1, $php->posts_count);
    }

    /** @test */
    public function it_has_popular_scope()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        
        $tag1 = Tag::create(['name' => 'Laravel']);
        $tag2 = Tag::create(['name' => 'PHP']);
        $tag3 = Tag::create(['name' => 'JavaScript']);
        
        // Create posts
        $post1 = Post::create([
            'title' => 'Post 1',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);
        
        $post2 = Post::create([
            'title' => 'Post 2',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        $post3 = Post::create([
            'title' => 'Post 3',
            'content' => 'Content',
            'status' => 'published',
            'user_id' => $user->id,
            'category_id' => $category->id
        ]);

        // Attach posts to tags (Laravel = 3 posts, PHP = 2 posts, JavaScript = 1 post)
        $tag1->posts()->attach([$post1->id, $post2->id, $post3->id]);
        $tag2->posts()->attach([$post1->id, $post2->id]);
        $tag3->posts()->attach([$post1->id]);

        $popularTags = Tag::popular(2)->get();
        
        $this->assertCount(2, $popularTags);
        $this->assertEquals('Laravel', $popularTags->first()->name);
        $this->assertEquals('PHP', $popularTags->get(1)->name);
        $this->assertEquals(3, $popularTags->first()->posts_count);
        $this->assertEquals(2, $popularTags->get(1)->posts_count);
    }

    /** @test */
    public function it_handles_special_characters_in_slug_generation()
    {
        $tag = Tag::create([
            'name' => 'C++ Programming!'
        ]);

        $this->assertEquals('c-programming', $tag->slug);
    }

    /** @test */
    public function it_handles_empty_slug_with_existing_name()
    {
        $tag = new Tag();
        $tag->name = 'Vue.js';
        $tag->slug = '';
        $tag->save();

        $this->assertEquals('vuejs', $tag->fresh()->slug);
    }

    /** @test */
    public function it_formats_manual_slug_properly()
    {
        $tag = Tag::create([
            'name' => 'React',
            'slug' => 'React Framework!'
        ]);

        $this->assertEquals('react-framework', $tag->slug);
    }

    /** @test */
    public function popular_scope_respects_limit_parameter()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        
        // Create 5 tags
        for ($i = 1; $i <= 5; $i++) {
            $tag = Tag::create(['name' => "Tag $i"]);
            
            // Create posts for each tag (decreasing number)
            for ($j = 0; $j < (6 - $i); $j++) {
                $post = Post::create([
                    'title' => "Post $i-$j",
                    'content' => 'Content',
                    'status' => 'published',
                    'user_id' => $user->id,
                    'category_id' => $category->id
                ]);
                $tag->posts()->attach($post->id);
            }
        }

        $popularTags = Tag::popular(3)->get();
        
        $this->assertCount(3, $popularTags);
        $this->assertEquals('Tag 1', $popularTags->first()->name);
        $this->assertEquals(5, $popularTags->first()->posts_count);
    }

    /** @test */
    public function popular_scope_has_default_limit()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Technology']);
        
        // Create 15 tags with posts
        for ($i = 1; $i <= 15; $i++) {
            $tag = Tag::create(['name' => "Tag $i"]);
            $post = Post::create([
                'title' => "Post $i",
                'content' => 'Content',
                'status' => 'published',
                'user_id' => $user->id,
                'category_id' => $category->id
            ]);
            $tag->posts()->attach($post->id);
        }

        $popularTags = Tag::popular()->get();
        
        // Default limit should be 10
        $this->assertCount(10, $popularTags);
    }
}