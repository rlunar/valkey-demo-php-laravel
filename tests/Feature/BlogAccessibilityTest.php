<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the blog page loads successfully and returns proper Inertia response.
     */
    public function test_blog_page_loads_successfully(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('blog/index'));
    }

    /**
     * Test that blog data includes all necessary fields for accessibility.
     */
    public function test_blog_data_includes_accessibility_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        // Test that all required data is present for accessibility features
        $response->assertInertia(fn ($page) => $page
            ->has('blog.siteName')
            ->has('blog.categories')
            ->has('blog.featuredPost.title')
            ->has('blog.featuredPost.excerpt')
            ->has('blog.featuredPost.readMoreUrl')
            ->has('blog.secondaryPosts')
            ->has('blog.mainPosts')
            ->has('blog.sidebar.aboutText')
            ->has('blog.sidebar.recentPosts')
            ->has('blog.sidebar.archives')
            ->has('blog.sidebar.externalLinks')
            ->has('blog.pagination')
        );
    }

    /**
     * Test that post data includes proper fields for accessibility.
     */
    public function test_post_data_includes_accessibility_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        // Test secondary posts have required fields
        $response->assertInertia(fn ($page) => $page
            ->has('blog.secondaryPosts.0.id')
            ->has('blog.secondaryPosts.0.title')
            ->has('blog.secondaryPosts.0.category')
            ->has('blog.secondaryPosts.0.date')
            ->has('blog.secondaryPosts.0.excerpt')
            ->has('blog.secondaryPosts.0.readMoreUrl')
        );

        // Test main posts have required fields
        $response->assertInertia(fn ($page) => $page
            ->has('blog.mainPosts.0.id')
            ->has('blog.mainPosts.0.title')
            ->has('blog.mainPosts.0.author')
            ->has('blog.mainPosts.0.date')
            ->has('blog.mainPosts.0.content')
        );
    }

    /**
     * Test that sidebar data includes proper fields for accessibility.
     */
    public function test_sidebar_data_includes_accessibility_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        // Test recent posts have required fields
        $response->assertInertia(fn ($page) => $page
            ->has('blog.sidebar.recentPosts.0.title')
            ->has('blog.sidebar.recentPosts.0.date')
            ->has('blog.sidebar.recentPosts.0.url')
        );

        // Test archives have required fields
        $response->assertInertia(fn ($page) => $page
            ->has('blog.sidebar.archives.0.label')
            ->has('blog.sidebar.archives.0.url')
        );

        // Test external links have required fields
        $response->assertInertia(fn ($page) => $page
            ->has('blog.sidebar.externalLinks.0.label')
            ->has('blog.sidebar.externalLinks.0.url')
        );
    }

    /**
     * Test that pagination data includes proper fields for accessibility.
     */
    public function test_pagination_data_includes_accessibility_fields(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->has('blog.pagination.hasOlder')
            ->has('blog.pagination.hasNewer')
            ->where('blog.pagination.hasOlder', true)
            ->where('blog.pagination.hasNewer', false)
            ->has('blog.pagination.olderUrl')
        );
    }

    /**
     * Test that categories array is properly structured for navigation.
     */
    public function test_categories_are_properly_structured(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        $response->assertInertia(fn ($page) => $page
            ->where('blog.categories', [
                'World',
                'U.S.',
                'Technology',
                'Design',
                'Culture',
                'Business',
                'Politics',
                'Opinion',
                'Science',
                'Health',
                'Style',
                'Travel'
            ])
        );
    }

    /**
     * Test that all post IDs are unique for proper accessibility labeling.
     */
    public function test_post_ids_are_unique(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $secondaryPostIds = collect($page->toArray()['props']['blog']['secondaryPosts'])
                ->pluck('id')
                ->toArray();

            $mainPostIds = collect($page->toArray()['props']['blog']['mainPosts'])
                ->pluck('id')
                ->toArray();

            $allIds = array_merge($secondaryPostIds, $mainPostIds);

            // Check that all IDs are unique
            $this->assertEquals(count($allIds), count(array_unique($allIds)));

            return $page;
        });
    }

    /**
     * Test that dates are in proper format for datetime attributes.
     */
    public function test_dates_are_properly_formatted(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $mainPosts = $page->toArray()['props']['blog']['mainPosts'];

            foreach ($mainPosts as $post) {
                // Check that date is in YYYY-MM-DD format (ISO date)
                $this->assertMatchesRegularExpression(
                    '/^\d{4}-\d{2}-\d{2}$/',
                    $post['date'],
                    "Post date should be in YYYY-MM-DD format for proper datetime attributes"
                );
            }

            return $page;
        });
    }

    /**
     * Test that external links are properly identified.
     */
    public function test_external_links_are_identified(): void
    {
        $response = $this->get('/blog');

        $response->assertStatus(200);

        $response->assertInertia(function ($page) {
            $externalLinks = $page->toArray()['props']['blog']['sidebar']['externalLinks'];

            foreach ($externalLinks as $link) {
                // Check that external links start with http:// or https://
                $this->assertMatchesRegularExpression(
                    '/^https?:\/\//',
                    $link['url'],
                    "External links should start with http:// or https://"
                );
            }

            return $page;
        });
    }
}
