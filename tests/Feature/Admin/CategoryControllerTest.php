<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Category;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment before each test method.
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test to ensure a list of categories is returned
     * 
     * @return void
     */
    public function test_it_should_return_a_list_of_categories(): void
    {
        Category::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => ['id', 'name', 'slug', 'image']
                        ],
                        'links'
                    ]
                ]);
                $this->assertCount(5, $response->json('data.data'));
    }

    /**
     * Test to ensure categories can be searched and the correct ones are returned.
     * 
     * @return void
     */
    public function test_it_should_return_categories_with_search_query(): void
    {
        Category::factory()->create(['name' => 'Category One']);
        Category::factory()->create(['name' => 'Category Two']);
        Category::factory()->create(['name' => 'Coki Pardede']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/categories?q=Category');

        $response->assertStatus(200)
                ->assertJsonCount(2, 'data.data');
    }

    /**
     * Test to ensure an empty list is returned when no categories are found.
     * 
     * @return void
     */
    public function test_it_should_returns_empty_list_if_no_categories_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/categories');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Categories',
                    'data' => [
                        'data' => []
                    ]
                ]);
    }

    /**
     * Test to ensure a new category can be stored.
     * 
     * @return void
     */
    public function test_it_should_store_a_new_category(): void
    {
        $file = UploadedFile::fake()->image('category.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/admin/categories', [
                            'image' => $file,
                            'name' => 'New Category'
                        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Data Category Berhasil Disimpan!'
        ]);

        Storage::disk('local')->assertExists('public/categories/' . $file->hashName());
        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
            'slug' => 'new-category'
        ]);
    }

    /**
     * Test to ensure validation occurs when creating a category.
     * 
     * @return void
     */
    public function test_it_validates_when_creating_a_category(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/admin/categories', [
                            'name' => '',
                            'image' => ''
                        ]);
        
        // dd($response->content());
        $response->assertStatus(422)
                ->assertJson([
                    'name' => ['The name field is required.'],
                    'image' => ['The image field is required.'],
                ]);
    }

    /**
     * Test to ensure a category can be retrieved by its ID.
     * 
     * @return void
     */
    public function test_it_can_show_a_category_by_id(): void
    {
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Detail Data Category!',
                    'data' => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'image' => $category->image
                    ]
                ]);
    }

    /**
     * Test to ensure the correct response is returned if a category is not found 
     * 
     * @return void
     */
    public function test_it_should_returns_404_if_category_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/categories/10394');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data Category Tidak Ditemukan!'
                ]);
    }

    /**
     * Test to ensure a category can be updated without changing its image.
     * 
     * @return void
     */
    public function test_it_can_update_a_category_without_image(): void
    {
        $category = Category::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/admin/categories/{$category->id}", [
                            'name' => 'Updated Category'
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Category Berhasil Diupdate!',
                ]);
    
        $responseData = $response->json('data');
        
        $this->assertEquals('Updated Category', $responseData['name']);
        $this->assertEquals('updated-category', $responseData['slug']);
        $this->assertEquals($category->image, $responseData['image']); // Ensure the image remains the same
    
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
        ]);
    }

    /**
     * Test to ensure a category can be updated along with its image.
     * 
     * @return void
     */
    public function test_it_can_update_a_category_with_image(): void
    {
        $category = Category::factory()->create();

        $newImage = UploadedFile::fake()->image('new-image.jpg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/admin/categories/{$category->id}", [
                            'name' => 'Updated Category Name',
                            'image' => $newImage
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Category Berhasil Diupdate!',
                ]);
        
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category Name',
            'slug' => 'updated-category-name',
        ]);

        Storage::disk('local')->assertMissing('public/categories/' . 'old_image.jpg');
        Storage::disk('local')->assertExists('public/categories/' . $newImage->hashName());
    }

    public function test_it_can_delete_a_category(): void
    {
        $category = Category::factory()->create([
            'image' => 'categories/test_image.jpg',
        ]);

        // Ensure the file exists in storage
        Storage::disk('local')->put('public/categories/test_image.jpg', 'dummy content');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson("/api/admin/categories/{$category->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Category Berhasil Dihapus!'
                ]);

        // Ensure the category is deleted from the database
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id
        ]);

        // Ensure the image file is deleted from storage
        Storage::disk('local')->assertMissing('public/categories/test_image.jpg');
    }

    public function test_it_should_returns_404_on_delete_if_category_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson('/api/admin/categories/10394');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Category Tidak Ditemukan!'
                ]);
    }
}
