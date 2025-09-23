<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;

class CleanupExtraCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'categories:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up extra categories created by factories, keeping only the original seeded categories';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define the original categories from the seeder
        $originalCategories = [
            'Development',
            'Performance', 
            'Architecture',
            'Redis Compatibility',
            'Open Source',
            'Tutorials',
            'News & Updates',
            'Community'
        ];

        $this->info('Analyzing categories...');
        
        // Get all categories
        $allCategories = Category::all();
        $originalCategoryIds = Category::whereIn('name', $originalCategories)->pluck('id')->toArray();
        
        // Find extra categories (not in the original list)
        $extraCategories = $allCategories->whereNotIn('id', $originalCategoryIds);
        
        $this->info("Total categories: {$allCategories->count()}");
        $this->info("Original categories: " . count($originalCategoryIds));
        $this->info("Extra categories to clean up: {$extraCategories->count()}");
        
        if ($extraCategories->isEmpty()) {
            $this->info('No extra categories found. Database is clean!');
            return 0;
        }

        // Show what will be affected
        $this->table(['ID', 'Name', 'Posts Count'], 
            $extraCategories->map(function ($category) {
                return [
                    $category->id,
                    $category->name,
                    $category->posts()->count()
                ];
            })->toArray()
        );

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No changes were made. Remove --dry-run to actually delete these categories.');
            return 0;
        }

        // Confirm deletion
        if (!$this->confirm('Do you want to proceed with deleting these extra categories?')) {
            $this->info('Operation cancelled.');
            return 0;
        }

        // Reassign posts from extra categories to a default category
        $defaultCategory = Category::whereIn('name', $originalCategories)->first();
        
        if (!$defaultCategory) {
            $this->error('No original categories found! Please run the CategorySeeder first.');
            return 1;
        }

        $this->info("Reassigning posts to default category: {$defaultCategory->name}");
        
        $totalPostsReassigned = 0;
        foreach ($extraCategories as $category) {
            $postsCount = $category->posts()->count();
            if ($postsCount > 0) {
                $category->posts()->update(['category_id' => $defaultCategory->id]);
                $totalPostsReassigned += $postsCount;
                $this->info("Reassigned {$postsCount} posts from '{$category->name}'");
            }
        }

        // Delete the extra categories
        $deletedCount = 0;
        foreach ($extraCategories as $category) {
            $category->delete();
            $deletedCount++;
        }

        $this->info("Successfully deleted {$deletedCount} extra categories");
        $this->info("Reassigned {$totalPostsReassigned} posts to '{$defaultCategory->name}'");
        $this->info('Cleanup completed!');

        return 0;
    }
}
