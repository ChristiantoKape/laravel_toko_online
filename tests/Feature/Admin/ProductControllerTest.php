<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class ProductControllerTest extends TestCase
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
     * Test that the API endpoint returns a list of products.
     * 
     * @return void
     */
    public function test_should_return_a_list_of_products(): void
    {
        Product::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/products');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => ['id', 'title', 'slug', 'category_id', 'user_id', 'description', 'weight', 'price', 'stock', 'discount', 'created_at', 'updated_at']
                        ],
                        'links'
                    ]
                ]);
    }

    /**
     * Test that the API endpoint returns products based on a search query.
     *
     * @return void
     */
    public function test_should_return_products_with_search_query(): void
    {
        Product::factory()->create(['title' => 'Product One']);
        Product::factory()->create(['title' => 'Product Two']);
        Product::factory()->create(['title' => 'Product Three']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/products?q=Product One');
        
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data.data')
                ->assertJsonFragment(['title' => 'Product One'])
                ->assertJsonMissing(['title' => 'Another Product'])
                ->assertJsonMissing(['title' => 'Product Tv']);
    }

    /**
     * Test that the API endpoint returns an empty list if no products are found.
     *
     * @return void
     */
    public function test_should_returns_empty_list_if_no_products_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/products?q=Product One');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Products',
                    'data' => [
                        'data' => [],
                        'total' => 0,
                        'per_page' => 5,
                        'current_page' => 1,
                        'last_page' => 1,
                    ]
                ]);
    }

    /**
     * Test that validation fails when storing a product.
     *
     * @return void
     */
    public function test_should_fail_validation_on_store(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('api/admin/products', []);
    
        $response->assertStatus(422)
                ->assertJson([
                    'image' => ['The image field is required.'],
                    'title' => ['The title field is required.'],
                    'category_id' => ['The category id field is required.'],
                    'description' => ['The description field is required.'],
                    'weight' => ['The weight field is required.'],
                    'price' => ['The price field is required.'],
                    'stock' => ['The stock field is required.'],
                    'discount' => ['The discount field is required.'],
                ]);
    }

    /**
     * Test that a product can be stored successfully.
     * 
     * @return void
     */
    public function test_should_store_product_successfully(): void
    {
        $category = Category::factory()->create();

        $data = [
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'image' => UploadedFile::fake()->image('image.jpg'),
            'title' => 'Product One',
            'slug' => Str::slug('Product One', '-'),
            'description' => 'Description of Product One',
            'weight' => 1,
            'price' => 10000,
            'stock' => 10,
            'discount' => 10
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('api/admin/products', $data);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Product Berhasil Disimpan!',
                    'data' => [
                        'title' => 'Product One',
                        'slug' => 'product-one',
                    ]
                ]);
        
        Storage::disk('local')->assertExists('public/products/' . basename($response->json('data.image')));
    }

    /**
     * Test that a 404 response is returned for a nonexistent product when retrieving details.
     *
     * @return void
     */
    public function test_should_return_404_for_nonexistent_product_on_show(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('api/admin/products/909');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data Product Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that a product can be retrieved successfully.
     *
     * @return void
     */
    public function test_should_show_product_successfully(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson("api/admin/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Detail Data Product!',
                    'data' => [
                        'id' => $product->id,
                        'title' => $product->title,
                    ]
                ]);
    }

    /**
     * Test that a 404 response is returned for a nonexistent product when updating.
     *
     * @return void
     */
    public function test_should_return_404_for_nonexistent_product_on_update(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('api/admin/products/909', [
                'title' => 'Nonexistent Product',
                'category_id' => 1,
                'description' => 'Nonexistent Description',
                'weight' => 150,
                'price' => 300,
                'stock' => 20,
                'discount' => 10,
            ]);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Product Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that a product can be updated successfully withoud changing the image.
     * 
     * @return void
     */
    public function test_should_update_product_successfully_without_image(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $updateData = [
            'title' => 'Updated Product Title',
            'category_id' => $category->id,
            'description' => 'Updated description',
            'weight' => 500,
            'price' => 1000,
            'stock' => 10,
            'discount' => 5,
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Product Berhasil Diupdate!',
                ]);

        $responseData = $response->json('data');

        // Assert the response data
        $this->assertEquals('Updated Product Title', $responseData['title']);
        $this->assertEquals(Str::slug('Updated Product Title', '-'), $responseData['slug']);
        $this->assertEquals($category->id, $responseData['category_id']);
        $this->assertEquals(auth()->guard('api_admin')->user()->id, $responseData['user_id']);
        $this->assertEquals('Updated description', $responseData['description']);
        $this->assertEquals(500, $responseData['weight']);
        $this->assertEquals(1000, $responseData['price']);
        $this->assertEquals(10, $responseData['stock']);
        $this->assertEquals(5, $responseData['discount']);
        $this->assertEquals($product->image, $responseData['image']); // Ensure the image remains the same

        $this->assertDatabaseHas('products', array_merge($updateData, [
            'id' => $product->id,
            'slug' => Str::slug($updateData['title'], '-'),
            'user_id' => auth()->guard('api_admin')->user()->id,
            'image' => $product->image, // Existing image should remain the same
        ]));
    }

    /**
     * Test that a product can be updated successfully with a new image.
     *
     * @return void
     */
    public function test_should_update_product_successfully_with_image(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson("/api/admin/products/{$product->id}", [
                            'image' => UploadedFile::fake()->image('new_product.jpg'),
                            'title' => 'Updated Product',
                            'category_id' => $category->id,
                            'description' => 'Updated Description',
                            'weight' => 150,
                            'price' => 300,
                            'stock' => 20,
                            'discount' => 10,
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Product Berhasil Diupdate!',
                    'data' => [
                        'title' => 'Updated Product',
                        'description' => 'Updated Description'
                    ]
                ]);
        
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Product',
            'description' => 'Updated Description',
        ]);
        
        Storage::disk('local')->assertExists('public/products/' . basename($response->json('data.image')));
    }

    /**
     * Test that a 404 response is returned for a nonexistent product when deleting.
     *
     * @return void
     */
    public function test_should_return_404_for_nonexistent_product_on_destroy(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('api/admin/products/909');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Product Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that a product can be deleted successfully.
     *
     * @return void
     */
    public function test_should_destroy_product_successfully(): void
    {
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson("api/admin/products/{$product->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Product Berhasil Dihapus!',
                ]);
        
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);

        Storage::disk('local')->assertMissing('public/products/' . basename($product->image));
    }
}
