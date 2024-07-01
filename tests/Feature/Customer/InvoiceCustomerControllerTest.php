<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Invoice;
use App\Models\Customer;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceCustomerControllerTest extends TestCase
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
     * Test mengambil data invoice tanpa parameter query
     *
     * @return void
     */
    public function test_index_no_query_parameter()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'current_page',
                        'data' => [],
                        'links' => [],
                    ]
                ]);
    }

    /**
     * Test mengambil data invoice dengan parameter query
     * 
     * @return void
     */
    public function test_index_with_query_parameter()
    {
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice' => 'INV/20210801',
        ]);
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice' => 'INV/202108012',
        ]);
        Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice' => 'INV/202108013',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices?q=INV/20210801');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Invoices : '. $this->customer->name,
                    'data' => [
                        'data' => [
                            [
                                'invoice' => 'INV/20210801'
                            ]
                        ],
                        'total' => 3,
                        'per_page' => 5,
                        'current_page' => 1,
                        'last_page' => 1,
                    ]
                ]);
    }

    /**
     * Test paginasi data invoice
     * 
     * @return void
     */
    public function test_index_pagination()
    {
        Invoice::factory()->count(10)->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices');

        $response->assertStatus(200)
        ->assertJsonCount(5, 'data.data') // Expecting only 5 items per page
        ->assertJsonStructure([
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'invoice',
                        'customer_id',
                    ]
                ],
                'links',
            ],
        ]);
    }

    /**
     * Menguji pengambilan data invoice dengan snap token yang valid
     * 
     * @return void
     */
    public function test_show_with_valid_snap_token()
    {      
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices/' . $invoice->snap_token);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Detail Data Invoice : ' . $invoice->snap_token,
                    'data' => [
                        'id' => $invoice->id,
                        'snap_token' => $invoice->snap_token,
                    ]
                ]);
    }

    /**
     * Menguji pengambilan daya invoice dengan snap token yang tidak valid
     * 
     * @return void
     */
    public function test_show_with_invalid_snap_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices/invalid_snap_token');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Invoice Not Found',
                ]);
    }

    /**
     * Menguji pengambian data invoice yang tidak dimiliki pengguna
     * 
     * @return void
     */
    public function test_show_invoice_not_owned_by_user()
    {
        $otherCustomer = Customer::factory()->create();

        // Create invoice for other customer gunakan $this->customer->id
        $invoice = Invoice::factory()->create([
            'customer_id' => $otherCustomer->id,
            'snap_token' => 'different_token',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/customer/invoices/' . $invoice->snap_token);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Invoice Not Found',
                ]);
    }
}
