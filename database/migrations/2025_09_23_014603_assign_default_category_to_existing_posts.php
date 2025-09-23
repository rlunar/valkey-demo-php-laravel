<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Post;
use App\Models\Category;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use an existing predefined category instead of creating "Uncategorized"
        // Try to find a suitable default category from the predefined ones
        $defaultCategory = Category::whereIn('name', [
            'Development', 'Performance', 'Architecture', 'Redis Compatibility',
            'Open Source', 'Tutorials', 'News & Updates', 'Community'
        ])->first();

        // If no predefined categories exist yet, create a temporary one that will be cleaned up
        if (!$defaultCategory) {
            $defaultCategory = Category::firstOrCreate(
                ['name' => 'Development'],
                [
                    'description' => 'Software development tutorials, best practices, and coding insights',
                    'color' => '#007bff'
                ]
            );
        }

        // Assign the default category to all posts that don't have a category
        Post::whereNull('category_id')->update(['category_id' => $defaultCategory->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set category_id to null for posts that were assigned by this migration
        // Since we're using predefined categories, we don't delete them in rollback
        Post::whereNotNull('category_id')->update(['category_id' => null]);
    }
};
