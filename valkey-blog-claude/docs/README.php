<?php

// Database Migrations

// 1. Create users table migration (extends default Laravel users)
// php artisan make:migration add_role_to_users_table

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'author'])->default('author')->after('email_verified_at');
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->string('website')->nullable()->after('bio');
            $table->string('twitter')->nullable()->after('website');
            $table->string('github')->nullable()->after('twitter');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'bio', 'website', 'twitter', 'github']);
        });
    }
}

// 2. Create categories table migration
// php artisan make:migration create_categories_table

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#6366f1'); // Hex color for UI
            $table->string('icon')->nullable(); // For Filament icons
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}

// 3. Create tags table migration
// php artisan make:migration create_tags_table

class CreateTagsTable extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#10b981'); // Hex color for UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tags');
    }
}

// 4. Create posts table migration
// php artisan make:migration create_posts_table

class CreatePostsTable extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // For multiple images
            $table->enum('status', ['draft', 'published', 'scheduled', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('reading_time')->nullable(); // In minutes
            $table->json('meta_data')->nullable(); // SEO meta, custom fields
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'published_at']);
            $table->index('user_id');
            $table->index('category_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
}

// 5. Create post_tag pivot table migration
// php artisan make:migration create_post_tag_table

class CreatePostTagTable extends Migration
{
    public function up()
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['post_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_tag');
    }
}

// Models

// User Model (app/Models/User.php) - Extend existing
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'bio',
        'website',
        'twitter',
        'github',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Filament user authorization
    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'author']);
    }

    // Relationships
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->role === 'author';
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=6366f1&color=fff';
    }
}

// Category Model (app/Models/Category.php)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Automatically generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->where('status', 'published')->count();
    }
}

// Tag Model (app/Models/Tag.php)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Automatically generate slug from name
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    // Relationships
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->where('status', 'published')->count();
    }
}

// Post Model (app/Models/Post.php)
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'gallery',
        'status',
        'published_at',
        'views_count',
        'reading_time',
        'meta_data',
        'user_id',
        'category_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'gallery' => 'array',
        'meta_data' => 'array',
        'views_count' => 'integer',
        'reading_time' => 'integer',
    ];

    // Automatically generate slug and reading time
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if (empty($post->reading_time)) {
                $post->reading_time = static::calculateReadingTime($post->content);
            }
        });

        static::updating(function ($post) {
            if ($post->isDirty('title') && empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            if ($post->isDirty('content')) {
                $post->reading_time = static::calculateReadingTime($post->content);
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo
    {
        return $this->user(); // Alias for clarity
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('published_at', '>', now());
    }

    public function scopeByAuthor($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeWithTag($query, $tagId)
    {
        return $query->whereHas('tags', function ($q) use ($tagId) {
            $q->where('tags.id', $tagId);
        });
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->published_at > now();
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image 
            ? asset('storage/' . $this->featured_image)
            : null;
    }

    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content
        return Str::limit(strip_tags($this->content), 160);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    // Calculate estimated reading time
    public static function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));
        $wordsPerMinute = 200; // Average reading speed
        
        return max(1, ceil($wordCount / $wordsPerMinute));
    }

    // Get status color for UI
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'published' => 'success',
            'scheduled' => 'warning',
            'archived' => 'danger',
            default => 'gray',
        };
    }
}

// Database Seeders

// DatabaseSeeder (database/seeders/DatabaseSeeder.php)
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
        ]);
    }
}

// UserSeeder (database/seeders/UserSeeder.php)
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'bio' => 'System Administrator',
            'email_verified_at' => now(),
        ]);

        // Create author user
        User::create([
            'name' => 'John Author',
            'email' => 'author@example.com',
            'password' => Hash::make('password'),
            'role' => 'author',
            'bio' => 'Content Writer and Developer Advocate',
            'website' => 'https://example.com',
            'twitter' => 'johnauthor',
            'github' => 'johnauthor',
            'email_verified_at' => now(),
        ]);

        // Create additional authors using factory
        User::factory(5)->create([
            'role' => 'author',
        ]);
    }
}

// CategorySeeder (database/seeders/CategorySeeder.php)
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Technology',
                'description' => 'Latest tech trends and innovations',
                'color' => '#3b82f6',
                'icon' => 'heroicon-o-cpu-chip',
            ],
            [
                'name' => 'Open Source',
                'description' => 'Open source projects and community',
                'color' => '#10b981',
                'icon' => 'heroicon-o-code-bracket',
            ],
            [
                'name' => 'Databases',
                'description' => 'Database technologies and best practices',
                'color' => '#8b5cf6',
                'icon' => 'heroicon-o-circle-stack',
            ],
            [
                'name' => 'DevOps',
                'description' => 'Development operations and infrastructure',
                'color' => '#f59e0b',
                'icon' => 'heroicon-o-cog-6-tooth',
            ],
            [
                'name' => 'Tutorials',
                'description' => 'Step-by-step guides and tutorials',
                'color' => '#ef4444',
                'icon' => 'heroicon-o-academic-cap',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

// TagSeeder (database/seeders/TagSeeder.php)
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            ['name' => 'Laravel', 'color' => '#ff2d20'],
            ['name' => 'PHP', 'color' => '#777bb4'],
            ['name' => 'Redis', 'color' => '#dc382d'],
            ['name' => 'Valkey', 'color' => '#ff6b6b'],
            ['name' => 'AWS', 'color' => '#ff9900'],
            ['name' => 'Docker', 'color' => '#2496ed'],
            ['name' => 'Kubernetes', 'color' => '#326ce5'],
            ['name' => 'JavaScript', 'color' => '#f7df1e'],
            ['name' => 'Vue.js', 'color' => '#4fc08d'],
            ['name' => 'API', 'color' => '#009688'],
            ['name' => 'Performance', 'color' => '#ff5722'],
            ['name' => 'Security', 'color' => '#795548'],
            ['name' => 'Testing', 'color' => '#607d8b'],
            ['name' => 'Best Practices', 'color' => '#9c27b0'],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}

// PostSeeder (database/seeders/PostSeeder.php)
use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $authors = User::where('role', 'author')->get();
        $categories = Category::all();
        $tags = Tag::all();

        // Create sample posts
        $posts = [
            [
                'title' => 'Getting Started with Valkey: A Redis Fork',
                'content' => 'Valkey is an open-source in-memory data store, used as a database, cache, message broker, and streaming engine. It provides data structures such as strings, hashes, lists, sets, and more...',
                'status' => 'published',
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Laravel Performance Optimization with Caching',
                'content' => 'Learn how to optimize your Laravel applications using various caching strategies including Redis, Memcached, and file-based caching...',
                'status' => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Building Scalable APIs with Laravel and Valkey',
                'content' => 'This comprehensive guide will walk you through building high-performance APIs using Laravel framework with Valkey as the caching layer...',
                'status' => 'draft',
            ],
            [
                'title' => 'Container Orchestration with Kubernetes',
                'content' => 'Understanding Kubernetes fundamentals and how to deploy Laravel applications in containerized environments...',
                'status' => 'scheduled',
                'published_at' => now()->addDays(2),
            ],
        ];

        foreach ($posts as $postData) {
            $post = Post::create([
                'title' => $postData['title'],
                'content' => $postData['content'],
                'status' => $postData['status'],
                'published_at' => $postData['published_at'] ?? null,
                'user_id' => $authors->random()->id,
                'category_id' => $categories->random()->id,
                'excerpt' => Str::limit($postData['content'], 160),
            ]);

            // Attach random tags
            $randomTags = $tags->random(rand(2, 5));
            $post->tags()->attach($randomTags);
        }

        // Create additional posts using factory
        Post::factory(20)->create();
    }
}

// Model Factories

// UserFactory (database/factories/UserFactory.php) - Update existing
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => fake()->randomElement(['admin', 'author']),
            'bio' => fake()->paragraph(),
            'website' => fake()->optional()->url(),
            'twitter' => fake()->optional()->userName(),
            'github' => fake()->optional()->userName(),
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function author()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'author',
        ]);
    }
}

// PostFactory (database/factories/PostFactory.php)
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Category;

class PostFactory extends Factory
{
    public function definition(): array
    {
        $publishedAt = fake()->optional(0.8)->dateTimeBetween('-6 months', '+1 month');
        
        return [
            'title' => fake()->sentence(6, true),
            'content' => fake()->paragraphs(10, true),
            'excerpt' => fake()->paragraph(),
            'status' => fake()->randomElement(['draft', 'published', 'scheduled', 'archived']),
            'published_at' => $publishedAt,
            'views_count' => fake()->numberBetween(0, 10000),
            'user_id' => User::where('role', 'author')->inRandomOrder()->first()?->id ?? User::factory()->author(),
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
        ];
    }

    public function published()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }

    public function draft()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function scheduled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('now', '+1 month'),
        ]);
    }
}

// CategoryFactory (database/factories/CategoryFactory.php)
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'description' => fake()->paragraph(),
            'color' => fake()->hexColor(),
            'icon' => fake()->randomElement([
                'heroicon-o-cpu-chip',
                'heroicon-o-code-bracket',
                'heroicon-o-circle-stack',
                'heroicon-o-cog-6-tooth',
                'heroicon-o-academic-cap',
            ]),
            'is_active' => true,
        ];
    }
}

// TagFactory (database/factories/TagFactory.php)
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }
}

// Installation Commands and Setup

/*
Installation Steps:

1. Create Laravel project with Filament:
composer create-project laravel/laravel blog-app
cd blog-app
composer require filament/filament

2. Install and configure Filament:
php artisan filament:install --panels

3. Create migrations:
php artisan make:migration add_role_to_users_table
php artisan make:migration create_categories_table
php artisan make:migration create_tags_table
php artisan make:migration create_posts_table
php artisan make:migration create_post_tag_table

4. Create models with factories:
php artisan make:model Category -mfs
php artisan make:model Tag -mfs
php artisan make:model Post -mfs

5. Create seeders:
php artisan make:seeder UserSeeder
php artisan make:seeder CategorySeeder
php artisan make:seeder TagSeeder
php artisan make:seeder PostSeeder

6. Configure storage link:
php artisan storage:link

7. Run migrations and seeders:
php artisan migrate
php artisan db:seed

8. Create Filament admin user:
php artisan make:filament-user

Next steps would be creating Filament Resources for each model to manage the blog content.
*/