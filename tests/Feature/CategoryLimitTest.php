<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that only the predefined categories exist.
     */
    public function test_only_predefined_categories_exist(): void
    {
        // Run the category seeder
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);

        // Assert exactly 8 categories exist
        $this->assertEquals(8, Category::count());

        // Assert the specific categories exist
        $expectedCategories = [
            'Development',
            'Performance',
            'Architecture',
            'Redis Compatibility',
            'Open Source',
            'Tutorials',
            'News & Updates',
            'Community',
        ];

        foreach ($expectedCategories as $categoryName) {
            $this->assertTrue(
                Category::where('name', $categoryName)->exists(),
                "Category '{$categoryName}' should exist"
            );
        }
    }

    /**
     * Test that post factory uses existing categories instead of creating new ones.
     */
    public function test_post_factory_uses_existing_categories(): void
    {
        // Seed categories first
        $this->artisan('db:seed', ['--class' => 'CategorySeeder']);
        
        $initialCategoryCount = Category::count();
        
        // Create posts using factory
        \App\Models\Post::factory(10)->create();
        
        // Assert no new categories were created
        $this->assertEquals($initialCategoryCount, Category::count());
    }
}
