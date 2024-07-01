<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment before each test method
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test response when no customers exist
     * 
     * @return void
     */
    public function test_should_return_a_list_when_no_customers_exists(): void
    {
        Customer::query()->delete();

        // make request to index endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/customers');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Customer',
                    'data' => [
                        'data' => [],
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 5,
                        'total' => 0
                    ]
                ]);
    }

    /**
     * Test retrieving paginated list of customers without search query
     * 
     * @return void
     */
    public function test_should_return_a_list_without_search_query(): void
    {
        Customer::factory()->count(10)->create();

        // make request to index endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/customers');

        // assert status and structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data',
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);
    }

    /**
     * Test retrieving paginated list of customers with search query
     * 
     * @return void
     */
    public function test_should_return_a_list_with_search_query(): void
    {
        Customer::factory()->create(['name' => 'John Doe']);
        Customer::factory()->count(5)->create();

        // make request to index endpoint with search query
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/customers?q=John Doe');

        // assert status and structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data',
                        'current_page',
                        'last_page',
                        'per_page',
                        'total'
                    ]
                ]);

        // assert that only the searched customer is returned
        $this->assertCount(1, $response->json('data.data'));
    }
}
