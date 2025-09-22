<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'published_at',
        'user_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the user that owns the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Set the title attribute and automatically generate slug if needed.
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        
        // Only auto-generate slug if it's not manually set and title changed
        if (empty($this->attributes['slug']) || ($this->isDirty('title') && empty($this->attributes['slug']))) {
            $this->attributes['slug'] = $this->generateUniqueSlug($value);
        }
    }

    /**
     * Set the slug attribute with proper formatting.
     */
    public function setSlugAttribute($value)
    {
        // If slug is provided, use it (after formatting)
        if (!empty($value)) {
            $this->attributes['slug'] = Str::slug($value);
        }
        // If empty and we have a title, generate from title
        elseif (!empty($this->attributes['title'])) {
            $this->attributes['slug'] = $this->generateUniqueSlug($this->attributes['title']);
        }
    }

    /**
     * Generate a unique slug from the given title.
     */
    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        // Check for existing slugs and append counter if needed
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug already exists in the database.
     */
    private function slugExists($slug)
    {
        $query = static::where('slug', $slug);
        
        // If updating an existing post, exclude current post from check
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        return $query->exists();
    }
}
