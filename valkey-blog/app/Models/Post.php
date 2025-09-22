<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

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
        'category_id',
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
     * Get the category that owns the post.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the tags for the post.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
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

    /**
     * Get the formatted content with proper HTML rendering and Bootstrap typography.
     */
    public function getFormattedContentAttribute(): HtmlString
    {
        $content = $this->content;
        
        // Convert markdown-style formatting to HTML
        $content = $this->convertMarkdownToHtml($content);
        
        // Apply Bootstrap typography classes
        $content = $this->applyBootstrapClasses($content);
        
        // Sanitize content to prevent XSS while allowing safe HTML
        $content = $this->sanitizeContent($content);
        
        return new HtmlString($content);
    }

    /**
     * Convert basic markdown formatting to HTML.
     */
    private function convertMarkdownToHtml($content)
    {
        // Convert headers
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
        
        // Convert bold and italic
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Convert code blocks
        $content = preg_replace('/```(.*?)```/s', '<pre><code>$1</code></pre>', $content);
        $content = preg_replace('/`(.*?)`/', '<code>$1</code>', $content);
        
        // Convert blockquotes
        $content = preg_replace('/^> (.*$)/m', '<blockquote class="blockquote">$1</blockquote>', $content);
        
        // Convert line breaks to paragraphs
        $paragraphs = explode("\n\n", $content);
        $content = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                // Don't wrap already formatted elements in paragraphs
                if (!preg_match('/^<(h[1-6]|blockquote|pre|ul|ol|li)/', $paragraph)) {
                    $paragraph = '<p>' . nl2br($paragraph) . '</p>';
                } else {
                    $paragraph = nl2br($paragraph);
                }
                $content .= $paragraph . "\n";
            }
        }
        
        return $content;
    }

    /**
     * Apply Bootstrap typography classes to HTML elements.
     */
    private function applyBootstrapClasses($content)
    {
        // Add Bootstrap classes to headers
        $content = str_replace('<h1>', '<h1 class="display-4 mb-3">', $content);
        $content = str_replace('<h2>', '<h2 class="h2 mb-3">', $content);
        $content = str_replace('<h3>', '<h3 class="h3 mb-2">', $content);
        
        // Add Bootstrap classes to blockquotes
        $content = str_replace('<blockquote class="blockquote">', '<blockquote class="blockquote border-start border-primary border-4 ps-3 my-3">', $content);
        
        // Add Bootstrap classes to code blocks
        $content = str_replace('<pre>', '<pre class="bg-light p-3 rounded">', $content);
        $content = str_replace('<code>', '<code class="bg-light px-2 py-1 rounded">', $content);
        
        return $content;
    }

    /**
     * Sanitize content to prevent XSS while allowing safe HTML tags.
     */
    private function sanitizeContent($content)
    {
        // Define allowed HTML tags and attributes
        $allowedTags = [
            'p', 'br', 'strong', 'em', 'b', 'i', 'u', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'a', 'img'
        ];
        
        $allowedAttributes = [
            'class', 'href', 'src', 'alt', 'title', 'target'
        ];
        
        // Basic sanitization - strip dangerous tags while keeping safe ones
        $content = strip_tags($content, '<' . implode('><', $allowedTags) . '>');
        
        // Remove dangerous attributes (basic implementation)
        $content = preg_replace('/on\w+="[^"]*"/i', '', $content);
        $content = preg_replace('/javascript:/i', '', $content);
        
        return $content;
    }

    /**
     * Generate automatic excerpt if not provided.
     */
    public function getExcerptAttribute($value)
    {
        // If excerpt is manually set, return it
        if (!empty($value)) {
            return $value;
        }
        
        // Generate excerpt from content
        return $this->generateExcerpt();
    }

    /**
     * Generate excerpt from post content.
     */
    public function generateExcerpt($length = 160)
    {
        // Strip HTML tags and get plain text
        $plainText = strip_tags($this->content);
        
        // Remove extra whitespace
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        $plainText = trim($plainText);
        
        // Truncate to specified length
        if (strlen($plainText) <= $length) {
            return $plainText;
        }
        
        // Find the last complete word within the length limit
        $truncated = substr($plainText, 0, $length);
        $lastSpace = strrpos($truncated, ' ');
        
        if ($lastSpace !== false) {
            $truncated = substr($truncated, 0, $lastSpace);
        }
        
        return $truncated . '...';
    }

    /**
     * Get excerpt for different contexts (card, meta, etc.)
     */
    public function getExcerptForContext($context = 'default')
    {
        $lengths = [
            'card' => 120,
            'featured' => 150,
            'meta' => 160,
            'default' => 160
        ];
        
        $length = $lengths[$context] ?? $lengths['default'];
        
        // If excerpt is manually set, respect the context length
        if (!empty($this->attributes['excerpt'])) {
            $excerpt = $this->attributes['excerpt'];
            if (strlen($excerpt) <= $length) {
                return $excerpt;
            }
            
            // Truncate manual excerpt if it's too long for context
            $truncated = substr($excerpt, 0, $length);
            $lastSpace = strrpos($truncated, ' ');
            
            if ($lastSpace !== false) {
                $truncated = substr($truncated, 0, $lastSpace);
            }
            
            return $truncated . '...';
        }
        
        // Generate from content
        return $this->generateExcerpt($length);
    }
}
