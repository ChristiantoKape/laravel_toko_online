<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use App\Models\Review;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewCustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment before each test case.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create();
        $this->token = JWTAuth::fromUser($this->customer);
    }

    /**
     * Test if the API returns an error when the user is not authenticated
     * 
     * @return void
     */
    public function test_should_return_error_if_user_is_not_authenticated()
    {
        $response = $this->postJson('/api/customer/review', []);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test if the API returns an error when data is incomplete or invalid.
     * 
     * @return void
     */
    public function test_should_return_error_if_data_is_incomplete_or_invalid()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/customer/review', []);

        $response->assertStatus(422)
        ->assertJson([
                    'order_id' => ['The order id field is required.'],
                    'product_id' => ['The product id field is required.'],
                    'rating' => ['The rating field is required.'],
                    'review' => ['The review field is required.'],
                ]);
    }

    /**
     * Test if the API returns a 409 conflict status if the review already exists.
     * 
     * @return void
     */
    public function test_should_return_409_if_review_already_exists()
    {
        $review = Review::factory()->create([
            'customer_id' => $this->customer->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/customer/review', $review->toArray());

        $response->assertStatus(409)
                ->assertJson($review->toArray());
    }

    /**
     * Test if the API successfully stores a review.
     *
     * @return void
     */
    public function test_should_store_review_successfully()
    {
        $review = Review::factory()->make([
            'customer_id' => $this->customer->id
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/customer/review', $review->toArray());

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Review Berhasil Disimpan!',
                ]);

        $this->assertDatabaseHas('reviews', [
            'order_id' => $review->order_id,
            'product_id' => $review->product_id,
            'customer_id' => $this->customer->id,
        ]);
    }
}
