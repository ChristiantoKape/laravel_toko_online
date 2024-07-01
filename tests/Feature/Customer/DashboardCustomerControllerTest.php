<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use App\Models\Invoice;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardCustomerControllerTest extends TestCase
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
     * Test whether an unauthenticated user receives an error when accessing the dashboard.
     * 
     * @return void
     */
    public function test_should_return_error_if_user_is_not_authenticated()
    {
        $response = $this->getJson('/api/customer/dashboard');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /**
     * Test whether the dashboard returns no statistics when no invoices are present.
     * 
     * @return void
     */
    public function test_should_return_no_statistics_when_no_invoices_present()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/customer/dashboard');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Statistik Data',
                    'data' => [
                        'count' => [
                            'pending' => 0,
                            'success' => 0,
                            'expired' => 0,
                            'failed' => 0
                        ]
                    ]
                ]);
    }

    /**
     * Test whether the dashboard returns statistics for an authenticated user.
     *
     * @return void
     */
    public function test_should_return_statistics_for_authenticated_user()
    {
        Invoice::factory()->count(5)->create([
            'status' => 'pending',
            'customer_id' => $this->customer->id,
        ]);
        Invoice::factory()->count(3)->create([
            'status' => 'success',
            'customer_id' => $this->customer->id,
        ]);
        Invoice::factory()->count(2)->create([
            'status' => 'expired',
            'customer_id' => $this->customer->id,
        ]);
        Invoice::factory()->count(1)->create([
            'status' => 'failed',
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/customer/dashboard');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Statistik Data',
                    'data' => [
                        'count' => [
                            'pending' => 5,
                            'success' => 3,
                            'expired' => 2,
                            'failed' => 1
                        ]
                    ]
                ]);
    }
}