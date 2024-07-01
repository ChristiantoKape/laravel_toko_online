<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Review;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProductWebControllerTest extends TestCase
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
     * Test case for retrieving a list of products
     * 
     * @return void
     */
    public function test_returns_a_list_of_products()
    {
        $reviews = Review::factory()->count(5)->create();

        $response = $this->getJson('/api/web/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'current_page',
                        'data' => [
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
                                'reviews_avg_rating',
                                'reviews_count',
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
     * Test case for retrieving a list of products based on a search query.
     * 
     * @return void
     */
    public function test_returns_a_list_of_products_based_on_search_query()
    {
        $product = Product::factory()->count(10)->create();

        $response = $this->getJson('/api/web/products?q=' . $product->first()->title);

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'title' => $product->first()->title,
                ]);
    }

    /**
     * Test case for retrieving product details.
     * 
     * @return void
     */
    public function test_returns_product_details()
    {
        $product = Product::factory()->create();
        $reviews = Review::factory()->count(5)->create(['product_id' => $product->id]);

        $response = $this->getJson('/api/web/products/' . $product->slug);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
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
                        'reviews_avg_rating',
                        'reviews_count',
                        'category' => [
                            'id',
                            'name',
                            'slug',
                            'image',
                            'created_at',
                            'updated_at',
                        ],
                    ]
                    ]);
    }

    /**
     * Test case for returning 404 if the product is not found.
     */
    public function test_returns_404_if_products_not_found()
    {
        $response = $this->getJson('/api/web/products/unknown-product');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data Product Tidak Ditemukan!',
                ]);
    }
}