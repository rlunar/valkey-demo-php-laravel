<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Post;

class BlogSeeder extends Seeder
{
    public function run()
    {
        // Create categories
        $categories = [
            ['name' => 'Technology', 'color' => '#007bff'],
            ['name' => 'Laravel', 'color' => '#ff2d20'],
            ['name' => 'PHP', 'color' => '#777bb4'],
            ['name' => 'JavaScript', 'color' => '#f7df1e'],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create sample posts
        $posts = [
            [
                'title' => 'Getting Started with Laravel',
                'excerpt' => 'Learn the basics of Laravel framework and build your first application.',
                'content' => 'Laravel is a powerful PHP framework that makes web development enjoyable and creative. In this post, we will explore the fundamental concepts of Laravel and guide you through creating your first application...',
                'published' => true,
                'published_at' => now()->subDays(5),
                'category_id' => 2,
            ],
            [
                'title' => 'Modern JavaScript ES6+ Features',
                'excerpt' => 'Explore the latest JavaScript features that will improve your code quality.',
                'content' => 'JavaScript has evolved significantly over the years. ES6 and beyond have introduced many features that make JavaScript more powerful and easier to work with...',
                'published' => true,
                'published_at' => now()->subDays(3),
                'category_id' => 4,
            ],
            [
                'title' => 'Building RESTful APIs with PHP',
                'excerpt' => 'A comprehensive guide to creating robust RESTful APIs using PHP.',
                'content' => 'REST APIs are essential for modern web applications. In this tutorial, we will cover how to build scalable and secure RESTful APIs using PHP...',
                'published' => true,
                'published_at' => now()->subDays(1),
                'category_id' => 3,
            ],
        ];

        foreach ($posts as $postData) {
            Post::create($postData);
        }
    }
}
