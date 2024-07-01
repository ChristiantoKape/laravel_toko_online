<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Customer;
use Tymon\JWTAuth\Facades\JWTAuth;

class CategoryWebControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment before each test case.
     * 
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->token = JWTAuth::fromUser($this->customer);
    }

    /**
     * Test whether the API endpoint returns a list of categories
     * 
     * @return void
     */
    public function test_returns_a_list_of_categories()
    {
        Category::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/web/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                        ]
                    ]
                ]);
    }

    /**
     * Test whether the API endpoint returns a category along with its products,
     * reviews count, and average rating.
     * 
     * @return void
     */
    public function test_returns_category_with_products_and_reviews_count_and_avg_rating()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Review::factory()->count(5)->create(['product_id' => $product->id, 'rating' => 4]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/web/categories/' . $category->slug);

        $response->dump();

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'image',
                        'created_at',
                        'updated_at',
                        'products' => [
                            '*' => [
                                'id',
                                'category_id',
                                'user_id',
                                'image',
                                'title',
                                'slug',
                                'description',
                                'weight',
                                'price',
                                'stock',
                                'discount',
                                'created_at',
                                'updated_at',
                                'reviews_count',
                                'reviews_avg_rating',
                                'category' => [
                                    'id',
                                    'name',
                                    'slug',
                                    'image',
                                    'created_at',
                                    'updated_at',
                                ]
                            ]
                        ]
                    ]
                ]);
    }

    /**
     * Test whether the API endpoint returns an error when a category is not found.
     * 
     * @return void
     */
    public function test_returns_error_if_category_not_found()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/web/categories/unknown-category');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data Category Tidak DItemukan!',
                ]);
    }
}
