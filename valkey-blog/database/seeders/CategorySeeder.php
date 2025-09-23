<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Development',
                'description' => 'Software development tutorials, best practices, and coding insights',
                'color' => '#007bff',
            ],
            [
                'name' => 'Performance',
                'description' => 'Performance optimization, benchmarks, and scaling strategies',
                'color' => '#28a745',
            ],
            [
                'name' => 'Architecture',
                'description' => 'System architecture, design patterns, and infrastructure',
                'color' => '#dc3545',
            ],
            [
                'name' => 'Redis Compatibility',
                'description' => 'Redis compatibility features, migration guides, and comparisons',
                'color' => '#fd7e14',
            ],
            [
                'name' => 'Open Source',
                'description' => 'Open source community, contributions, and project updates',
                'color' => '#6f42c1',
            ],
            [
                'name' => 'Tutorials',
                'description' => 'Step-by-step guides and hands-on tutorials',
                'color' => '#20c997',
            ],
            [
                'name' => 'News & Updates',
                'description' => 'Latest news, releases, and project announcements',
                'color' => '#ffc107',
            ],
            [
                'name' => 'Community',
                'description' => 'Community highlights, events, and user stories',
                'color' => '#6c757d',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create some additional random categories
        Category::factory(4)->create();
    }
}