<?php

namespace Tests\Feature;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class PostRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $request = new PostRequest();
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_title_field()
    {
        $request = new PostRequest();
        
        // Test required title
        $validator = Validator::make(['title' => ''], $request->rules());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());

        // Test title max length
        $validator = Validator::make(['title' => str_repeat('a', 256)], $request->rules());
        $this->assertArrayHasKey('title', $validator->errors()->toArray());

        // Test valid title
        $validator = Validator::make(['title' => 'Valid Title'], $request->rules());
        $this->assertArrayNotHasKey('title', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_content_field()
    {
        $request = new PostRequest();
        
        // Test required content
        $validator = Validator::make(['content' => ''], $request->rules());
        $this->assertArrayHasKey('content', $validator->errors()->toArray());

        // Test valid content
        $validator = Validator::make(['content' => 'Valid content'], $request->rules());
        $this->assertArrayNotHasKey('content', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_excerpt_field()
    {
        $request = new PostRequest();
        
        // Test excerpt max length
        $validator = Validator::make(['excerpt' => str_repeat('a', 501)], $request->rules());
        $this->assertArrayHasKey('excerpt', $validator->errors()->toArray());

        // Test valid excerpt
        $validator = Validator::make(['excerpt' => 'Valid excerpt'], $request->rules());
        $this->assertArrayNotHasKey('excerpt', $validator->errors()->toArray());

        // Test null excerpt
        $validator = Validator::make(['excerpt' => null], $request->rules());
        $this->assertArrayNotHasKey('excerpt', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_status_field()
    {
        $request = new PostRequest();
        
        // Test invalid status
        $validator = Validator::make(['status' => 'invalid'], $request->rules());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());

        // Test valid statuses
        $validator = Validator::make(['status' => 'draft'], $request->rules());
        $this->assertArrayNotHasKey('status', $validator->errors()->toArray());

        $validator = Validator::make(['status' => 'published'], $request->rules());
        $this->assertArrayNotHasKey('status', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_slug_field()
    {
        $request = new PostRequest();
        
        // Test invalid slug format
        $validator = Validator::make(['slug' => 'Invalid Slug!'], $request->rules());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());

        // Test slug max length
        $validator = Validator::make(['slug' => str_repeat('a', 256)], $request->rules());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());

        // Test valid slug
        $validator = Validator::make(['slug' => 'valid-slug-123'], $request->rules());
        $this->assertArrayNotHasKey('slug', $validator->errors()->toArray());

        // Test null slug
        $validator = Validator::make(['slug' => null], $request->rules());
        $this->assertArrayNotHasKey('slug', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_slug_uniqueness()
    {
        $existingPost = Post::factory()->create(['slug' => 'existing-slug']);
        $request = new PostRequest();
        
        // Test duplicate slug
        $validator = Validator::make(['slug' => 'existing-slug'], $request->rules());
        $this->assertArrayHasKey('slug', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_category_id_field()
    {
        $request = new PostRequest();
        
        // Test required category_id
        $validator = Validator::make([], $request->rules());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test non-integer category_id
        $validator = Validator::make(['category_id' => 'not-integer'], $request->rules());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test non-existent category_id
        $validator = Validator::make(['category_id' => 99999], $request->rules());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        // Test valid category_id
        $validator = Validator::make(['category_id' => $this->category->id], $request->rules());
        $this->assertArrayNotHasKey('category_id', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_tags_field()
    {
        $request = new PostRequest();
        
        // Test non-array tags
        $validator = Validator::make(['tags' => 'not-array'], $request->rules());
        $this->assertArrayHasKey('tags', $validator->errors()->toArray());

        // Test valid tags array
        $validator = Validator::make(['tags' => ['tag1', 'tag2']], $request->rules());
        $this->assertArrayNotHasKey('tags', $validator->errors()->toArray());

        // Test null tags
        $validator = Validator::make(['tags' => null], $request->rules());
        $this->assertArrayNotHasKey('tags', $validator->errors()->toArray());

        // Test empty tags array
        $validator = Validator::make(['tags' => []], $request->rules());
        $this->assertArrayNotHasKey('tags', $validator->errors()->toArray());
    }

    /** @test */
    public function it_validates_individual_tags()
    {
        $request = new PostRequest();
        
        // Test non-string tag
        $validator = Validator::make(['tags' => [123]], $request->rules());
        $this->assertArrayHasKey('tags.0', $validator->errors()->toArray());

        // Test tag max length
        $validator = Validator::make(['tags' => [str_repeat('a', 51)]], $request->rules());
        $this->assertArrayHasKey('tags.0', $validator->errors()->toArray());

        // Test invalid tag characters
        $validator = Validator::make(['tags' => ['invalid@tag!']], $request->rules());
        $this->assertArrayHasKey('tags.0', $validator->errors()->toArray());

        // Test valid tags
        $validator = Validator::make(['tags' => ['valid-tag', 'another_tag', 'Tag With Spaces']], $request->rules());
        $this->assertArrayNotHasKey('tags.0', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('tags.1', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('tags.2', $validator->errors()->toArray());
    }

    /** @test */
    public function it_sanitizes_tags_during_preparation()
    {
        $request = new PostRequest();
        $request->merge([
            'tags' => [
                '  Tag With Spaces  ',
                'UPPERCASE TAG',
                'duplicate-tag',
                'duplicate-tag',
                '',
                '   ',
                'valid-tag'
            ]
        ]);

        // Simulate the prepareForValidation method
        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $sanitizedTags = $request->input('tags');

        $this->assertContains('tag with spaces', $sanitizedTags);
        $this->assertContains('uppercase tag', $sanitizedTags);
        $this->assertContains('duplicate-tag', $sanitizedTags);
        $this->assertContains('valid-tag', $sanitizedTags);
        
        // Should remove duplicates
        $this->assertEquals(1, array_count_values($sanitizedTags)['duplicate-tag']);
        
        // Should remove empty strings
        $this->assertNotContains('', $sanitizedTags);
        $this->assertNotContains('   ', $sanitizedTags);
    }

    /** @test */
    public function it_sets_published_at_for_published_posts()
    {
        $this->actingAs($this->user);
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'published',
            'category_id' => $this->category->id
        ];

        $request = PostRequest::create('/test', 'POST', $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        
        // Validate the request
        $validator = \Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
        
        // Set the validator on the request
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);
        $property->setValue($request, $validator);

        $validated = $request->validated();

        $this->assertArrayHasKey('published_at', $validated);
        $this->assertNotNull($validated['published_at']);
    }

    /** @test */
    public function it_sets_published_at_to_null_for_draft_posts()
    {
        $this->actingAs($this->user);
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
            'category_id' => $this->category->id
        ];

        $request = PostRequest::create('/test', 'POST', $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        
        // Validate the request
        $validator = \Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
        
        // Set the validator on the request
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);
        $property->setValue($request, $validator);

        $validated = $request->validated();

        $this->assertArrayHasKey('published_at', $validated);
        $this->assertNull($validated['published_at']);
    }

    /** @test */
    public function it_sets_user_id_for_new_posts()
    {
        $this->actingAs($this->user);
        
        $data = [
            'title' => 'Test Post',
            'content' => 'Test content',
            'status' => 'draft',
            'category_id' => $this->category->id
        ];

        $request = PostRequest::create('/test', 'POST', $data);
        $request->setContainer($this->app);
        $request->setRedirector($this->app['redirect']);
        
        // Validate the request
        $validator = \Validator::make($data, $request->rules());
        $this->assertTrue($validator->passes());
        
        // Set the validator on the request
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('validator');
        $property->setAccessible(true);
        $property->setValue($request, $validator);

        $validated = $request->validated();

        $this->assertArrayHasKey('user_id', $validated);
        $this->assertEquals($this->user->id, $validated['user_id']);
    }

    /** @test */
    public function it_provides_custom_error_messages()
    {
        $request = new PostRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('slug.regex', $messages);
        $this->assertArrayHasKey('slug.unique', $messages);
        $this->assertArrayHasKey('category_id.required', $messages);
        $this->assertArrayHasKey('category_id.exists', $messages);
        $this->assertArrayHasKey('tags.array', $messages);
        $this->assertArrayHasKey('tags.*.string', $messages);
        $this->assertArrayHasKey('tags.*.max', $messages);
        $this->assertArrayHasKey('tags.*.regex', $messages);
    }

    /** @test */
    public function it_authorizes_authenticated_users()
    {
        $request = new PostRequest();
        
        // Test unauthorized user
        $this->assertFalse($request->authorize());

        // Test authorized user
        $this->actingAs($this->user);
        $this->assertTrue($request->authorize());
    }
}