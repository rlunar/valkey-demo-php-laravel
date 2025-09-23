<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $categoryId = null;
        
        // Try to get the category ID from the route parameter
        try {
            $category = $this->route('category');
            if ($category) {
                $categoryId = is_object($category) ? $category->id : $category;
            }
        } catch (\Exception $e) {
            // Route parameter not available (e.g., during testing)
            $categoryId = null;
        }
        
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $categoryId
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:categories,slug,' . $categoryId
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-Fa-f]{6}$/'
            ]
        ];
    }

    /**
     * Get custom error messages for validation failures.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The category name is required.',
            'name.string' => 'The category name must be a valid text string.',
            'name.max' => 'The category name may not be greater than 255 characters.',
            'name.unique' => 'A category with this name already exists.',
            
            'slug.string' => 'The slug must be a valid text string.',
            'slug.max' => 'The slug may not be greater than 255 characters.',
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'A category with this slug already exists.',
            
            'description.string' => 'The description must be a valid text string.',
            'description.max' => 'The description may not be greater than 1000 characters.',
            
            'color.string' => 'The color must be a valid text string.',
            'color.regex' => 'The color must be a valid hex color code (e.g., #FF5733).'
        ];
    }

    /**
     * Get custom attribute names for validation errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'category name',
            'slug' => 'category slug',
            'description' => 'category description',
            'color' => 'category color'
        ];
    }
}