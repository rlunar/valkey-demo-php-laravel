<?php

namespace Tests\Feature;

use Tests\TestCase;

class BlogResponsiveTest extends TestCase
{
    public function test_blog_page_loads_successfully()
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
        );
    }

    public function test_blog_page_has_proper_viewport_meta_tag()
    {
        $response = $this->get('/blog');

        $response->assertSee('width=device-width, initial-scale=1', false);
    }



    public function test_blog_data_structure_is_complete()
    {
        $response = $this->get('/blog');

        $response->assertInertia(fn ($page) =>
            $page->component('blog/index')
                ->has('blog.siteName')
                ->has('blog.categories')
                ->has('blog.featuredPost.title')
                ->has('blog.featuredPost.excerpt')
                ->has('blog.featuredPost.readMoreUrl')
                ->has('blog.secondaryPosts.0.id')
                ->has('blog.secondaryPosts.0.title')
                ->has('blog.secondaryPosts.0.category')
                ->has('blog.mainPosts.0.id')
                ->has('blog.mainPosts.0.title')
                ->has('blog.mainPosts.0.author')
                ->has('blog.sidebar.aboutText')
                ->has('blog.sidebar.recentPosts')
                ->has('blog.sidebar.archives')
                ->has('blog.sidebar.externalLinks')
        );
    }

    public function test_responsive_css_utilities_exist()
    {
        // Test that our CSS includes responsive utilities
        $cssContent = file_get_contents(resource_path('css/app.css'));

        // Check for responsive utilities we added
        $this->assertStringContainsString('touch-manipulation', $cssContent);
        $this->assertStringContainsString('line-clamp-3', $cssContent);
        $this->assertStringContainsString('@media (min-width: 640px)', $cssContent);
        $this->assertStringContainsString('scroll-smooth', $cssContent);
    }
}
