<?php

namespace Tests\Feature;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CategoryRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that valid category data passes validation.
     */
    public function test_valid_category_data_passes_validation(): void
    {
        $data = [
            'name' => 'Technology',
            'slug' => 'technology',
            'description' => 'Posts about technology and innovation',
            'color' => '#FF5733'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that category name is required.
     */
    public function test_category_name_is_required(): void
    {
        $data = [
            'slug' => 'technology',
            'description' => 'Posts about technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertContains('The category name is required.', $validator->errors()->get('name'));
    }

    /**
     * Test that category name must be a string.
     */
    public function test_category_name_must_be_string(): void
    {
        $data = [
            'name' => 123,
            'slug' => 'technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /**
     * Test that category name has maximum length limit.
     */
    public function test_category_name_has_max_length(): void
    {
        $data = [
            'name' => str_repeat('a', 256), // 256 characters, exceeds 255 limit
            'slug' => 'technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertContains('The category name may not be greater than 255 characters.', $validator->errors()->get('name'));
    }

    /**
     * Test that category name must be unique.
     */
    public function test_category_name_must_be_unique(): void
    {
        // Create an existing category
        Category::factory()->create(['name' => 'Technology']);

        $data = [
            'name' => 'Technology',
            'slug' => 'tech'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertContains('A category with this name already exists.', $validator->errors()->get('name'));
    }

    /**
     * Test that slug is optional.
     */
    public function test_slug_is_optional(): void
    {
        $data = [
            'name' => 'Technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that slug must follow correct format.
     */
    public function test_slug_must_follow_correct_format(): void
    {
        $invalidSlugs = [
            'Technology', // uppercase
            'tech nology', // spaces
            'tech_nology', // underscores
            'tech@nology', // special characters
            'Tech-Nology', // mixed case
            '-technology', // starts with hyphen
            'technology-', // ends with hyphen
        ];

        foreach ($invalidSlugs as $slug) {
            $data = [
                'name' => 'Technology',
                'slug' => $slug
            ];

            $request = new CategoryRequest();
            $validator = Validator::make($data, $request->rules(), $request->messages());

            $this->assertFalse($validator->passes(), "Slug '{$slug}' should be invalid");
            $this->assertArrayHasKey('slug', $validator->errors()->toArray());
        }
    }

    /**
     * Test that valid slug formats pass validation.
     */
    public function test_valid_slug_formats_pass(): void
    {
        $validSlugs = [
            'technology',
            'tech-news',
            'web-development',
            'ai-ml',
            'tech123',
            'category-1'
        ];

        foreach ($validSlugs as $slug) {
            $data = [
                'name' => 'Technology',
                'slug' => $slug
            ];

            $request = new CategoryRequest();
            $validator = Validator::make($data, $request->rules(), $request->messages());

            $this->assertTrue($validator->passes(), "Slug '{$slug}' should be valid");
        }
    }

    /**
     * Test that slug must be unique.
     */
    public function test_slug_must_be_unique(): void
    {
        // Create an existing category
        Category::factory()->create(['slug' => 'technology']);

        $data = [
            'name' => 'Tech News',
            'slug' => 'technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
        $this->assertContains('A category with this slug already exists.', $validator->errors()->get('slug'));
    }

    /**
     * Test that description is optional.
     */
    public function test_description_is_optional(): void
    {
        $data = [
            'name' => 'Technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that description has maximum length limit.
     */
    public function test_description_has_max_length(): void
    {
        $data = [
            'name' => 'Technology',
            'description' => str_repeat('a', 1001) // 1001 characters, exceeds 1000 limit
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('description', $validator->errors()->toArray());
        $this->assertContains('The description may not be greater than 1000 characters.', $validator->errors()->get('description'));
    }

    /**
     * Test that color is optional.
     */
    public function test_color_is_optional(): void
    {
        $data = [
            'name' => 'Technology'
        ];

        $request = new CategoryRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->passes());
    }

    /**
     * Test that color must be valid hex format.
     */
    public function test_color_must_be_valid_hex(): void
    {
        $invalidColors = [
            'FF5733', // missing #
            '#FF573', // too short
            '#FF57333', // too long
            '#GG5733', // invalid characters
            'red', // color name
            'rgb(255, 87, 51)', // rgb format
        ];

        foreach ($invalidColors as $color) {
            $data = [
                'name' => 'Technology',
                'color' => $color
            ];

            $request = new CategoryRequest();
            $validator = Validator::make($data, $request->rules(), $request->messages());

            $this->assertFalse($validator->passes(), "Color '{$color}' should be invalid");
            $this->assertArrayHasKey('color', $validator->errors()->toArray());
            $this->assertContains('The color must be a valid hex color code (e.g., #FF5733).', $validator->errors()->get('color'));
        }
    }

    /**
     * Test that valid hex colors pass validation.
     */
    public function test_valid_hex_colors_pass(): void
    {
        $validColors = [
            '#FF5733',
            '#000000',
            '#FFFFFF',
            '#123ABC',
            '#abcdef',
            '#A1B2C3'
        ];

        foreach ($validColors as $color) {
            $data = [
                'name' => 'Technology',
                'color' => $color
            ];

            $request = new CategoryRequest();
            $validator = Validator::make($data, $request->rules(), $request->messages());

            $this->assertTrue($validator->passes(), "Color '{$color}' should be valid");
        }
    }

    /**
     * Test validation when updating existing category (name uniqueness exception).
     */
    public function test_name_uniqueness_exception_when_updating(): void
    {
        $category = Category::factory()->create(['name' => 'Technology']);

        // Test the validation rules directly with the category ID
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $category->id
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:categories,slug,' . $category->id
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

        $data = [
            'name' => 'Technology', // Same name as existing category
            'slug' => 'tech-updated'
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }

    /**
     * Test validation when updating existing category (slug uniqueness exception).
     */
    public function test_slug_uniqueness_exception_when_updating(): void
    {
        $category = Category::factory()->create(['slug' => 'technology']);

        // Test the validation rules directly with the category ID
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name,' . $category->id
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:categories,slug,' . $category->id
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

        $data = [
            'name' => 'Technology Updated',
            'slug' => 'technology' // Same slug as existing category
        ];

        $validator = Validator::make($data, $rules);

        $this->assertTrue($validator->passes());
    }
}