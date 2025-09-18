<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogIntegrationTest extends TestCase
{
    /**
     * Test that the blog page loads successfully.
     */
    public function test_blog_page_loads_successfully(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog')
                ->has('blog.siteName')
                ->has('blog.categories')
                ->has('blog.featuredPost')
                ->has('blog.secondaryPosts')
                ->has('blog.mainPosts')
                ->has('blog.sidebar')
                ->has('blog.pagination')
        );
    }

    /**
     * Test that blog data structure is correct.
     */
    public function test_blog_data_structure_is_correct(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->where('blog.siteName', 'Large')
                ->has('blog.categories', 12) // Should have 12 categories
                ->has('blog.secondaryPosts', 2) // Should have 2 secondary posts
                ->has('blog.mainPosts', 3) // Should have 3 main posts
                ->has('blog.sidebar.recentPosts', 3) // Should have 3 recent posts
                ->has('blog.sidebar.archives', 12) // Should have 12 archive links
                ->has('blog.sidebar.externalLinks', 4) // Should have 4 external links
        );
    }

    /**
     * Test that featured post has required fields.
     */
    public function test_featured_post_has_required_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.featuredPost.title')
                ->has('blog.featuredPost.excerpt')
                ->has('blog.featuredPost.readMoreUrl')
        );
    }

    /**
     * Test that secondary posts have required fields.
     */
    public function test_secondary_posts_have_required_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.secondaryPosts.0.id')
                ->has('blog.secondaryPosts.0.title')
                ->has('blog.secondaryPosts.0.category')
                ->has('blog.secondaryPosts.0.date')
                ->has('blog.secondaryPosts.0.excerpt')
                ->has('blog.secondaryPosts.0.readMoreUrl')
        );
    }

    /**
     * Test that main posts have required fields.
     */
    public function test_main_posts_have_required_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.mainPosts.0.id')
                ->has('blog.mainPosts.0.title')
                ->has('blog.mainPosts.0.author')
                ->has('blog.mainPosts.0.date')
                ->has('blog.mainPosts.0.content')
        );
    }

    /**
     * Test that sidebar has all required sections.
     */
    public function test_sidebar_has_all_required_sections(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.sidebar.aboutText')
                ->has('blog.sidebar.recentPosts')
                ->has('blog.sidebar.archives')
                ->has('blog.sidebar.externalLinks')
        );
    }

    /**
     * Test that pagination data is present.
     */
    public function test_pagination_data_is_present(): void
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.pagination.hasOlder')
                ->has('blog.pagination.hasNewer')
                ->has('blog.pagination.olderUrl')
                ->has('blog.pagination.newerUrl')
        );
    }
}
