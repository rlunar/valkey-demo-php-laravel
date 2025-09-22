<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $postId = $this->route('post') ? $this->route('post')->id : null;

        return [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'status' => 'required|in:draft,published',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('posts', 'slug')->ignore($postId)
            ],
            'category_id' => 'required|integer|exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50|regex:/^[a-zA-Z0-9\s\-_]+$/',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug must only contain lowercase letters, numbers, and hyphens, and cannot start or end with a hyphen.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            'category_id.required' => 'Please select a category for this post.',
            'category_id.exists' => 'The selected category does not exist.',
            'tags.array' => 'Tags must be provided as an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag must not exceed 50 characters.',
            'tags.*.regex' => 'Tags can only contain letters, numbers, spaces, hyphens, and underscores.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize tags if provided
        if ($this->has('tags') && is_array($this->tags)) {
            $sanitizedTags = [];
            foreach ($this->tags as $tag) {
                if (is_string($tag)) {
                    // Trim whitespace and convert to lowercase for consistency
                    $sanitizedTag = trim(strtolower($tag));
                    if (!empty($sanitizedTag)) {
                        $sanitizedTags[] = $sanitizedTag;
                    }
                }
            }
            // Remove duplicates
            $this->merge([
                'tags' => array_unique($sanitizedTags)
            ]);
        }
    }

    /**
     * Get the validated data from the request with additional processing.
     *
     * @param array|null $key
     * @param mixed $default
     * @return array
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Only process if we have validated data
        if (is_array($validated)) {
            // Set published_at if status is published
            if (isset($validated['status']) && $validated['status'] === 'published') {
                $validated['published_at'] = now();
            } elseif (isset($validated['status']) && $validated['status'] === 'draft') {
                $validated['published_at'] = null;
            }

            // Set the current authenticated user as the author for new posts
            if (!$this->route('post')) {
                $validated['user_id'] = auth()->id();
            }
        }

        return $validated;
    }
}