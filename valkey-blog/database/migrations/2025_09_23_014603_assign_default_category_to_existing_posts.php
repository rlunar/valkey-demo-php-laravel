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
        // Find or create a default "Uncategorized" category
        $defaultCategory = Category::firstOrCreate(
            ['name' => 'Uncategorized'],
            [
                'description' => 'Posts that have not been assigned to a specific category',
                'color' => '#6c757d'
            ]
        );

        // Assign the default category to all posts that don't have a category
        Post::whereNull('category_id')->update(['category_id' => $defaultCategory->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Find the "Uncategorized" category
        $uncategorizedCategory = Category::where('name', 'Uncategorized')->first();
        
        if ($uncategorizedCategory) {
            // Set category_id to null for posts that were assigned to "Uncategorized"
            Post::where('category_id', $uncategorizedCategory->id)->update(['category_id' => null]);
            
            // Optionally delete the "Uncategorized" category if it was created by this migration
            // Only delete if it has no posts assigned (in case other posts were manually assigned to it)
            if ($uncategorizedCategory->posts()->count() === 0) {
                $uncategorizedCategory->delete();
            }
        }
    }
};
