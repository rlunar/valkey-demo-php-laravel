<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    /**
     * Get the posts for the category.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Scope a query to include post count for each category.
     */
    public function scopeWithPostCount(Builder $query): Builder
    {
        return $query->withCount('posts');
    }

    /**
     * Set the name attribute and automatically generate slug if needed.
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        
        // Only auto-generate slug if it's not set, null, or empty string
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = $this->generateUniqueSlug($value);
        }
    }

    /**
     * Set the slug attribute with proper formatting.
     */
    public function setSlugAttribute($value)
    {
        // If slug is provided, use it (after formatting) and ensure uniqueness
        if (!empty($value)) {
            $formattedSlug = Str::slug($value);
            $this->attributes['slug'] = $this->generateUniqueSlug($formattedSlug, true);
        }
        // If empty and we have a name, generate from name
        elseif (!empty($this->attributes['name'])) {
            $this->attributes['slug'] = $this->generateUniqueSlug($this->attributes['name']);
        }
    }

    /**
     * Generate a unique slug from the given name.
     */
    private function generateUniqueSlug($name, $isManualSlug = false)
    {
        $slug = $isManualSlug ? $name : Str::slug($name);
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
        
        // If updating an existing category, exclude current category from check
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }
        
        return $query->exists();
    }
}