<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Invoice;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class InvoiceControllerTest extends TestCase
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

        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test whether the API returns an empty list of invoices when there are no invoices.
     * 
     * @return void
     */
    public function test_should_return_empty_list_if_no_invoices(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices');

        $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'List Data Invoices',
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
     * Test whether the API return an empty list of invoices when querying with a non-existent invoice number.
     * 
     * @return void
     */
    public function test_should_return_empty_list_if_no_invoices_match_query(): void
    {
        Invoice::factory()->create(['invoice' => 'INV-54321']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices?q=INV-12345');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Invoices',
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
     * Test whether the API successfully lists invoices.
     * 
     * @return void
     */
    public function test_should_list_invoices(): void
    {
        Invoice::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => ['id', 'invoice', 'customer_id', 'courier', 'courier_service', 'courier_cost', 'weight', 'name', 'phone', 'address', 'city_id', 'province_id', 'address', 'status', 'grand_total', 'snap_token', 'created_at', 'updated_at']
                        ],
                        'links'
                    ]
                    ]);
    }

    /**
     * Test whether the API filters invoices by query (invoice number).
     * 
     * @return void
     */
    public function test_should_filter_invoices_by_query(): void
    {
        Invoice::factory()->create(['invoice' => 'INV-54321']);
        Invoice::factory()->create(['invoice' => 'INV-12345']);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices?q=INV-12345');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Invoices',
                    'data' => [
                        'data' => [
                            [
                                'invoice' => 'INV-12345'
                            ]
                        ],
                        'total' => 1,
                        'per_page' => 5,
                        'current_page' => 1,
                        'last_page' => 1,
                    ]
                ]);
    }

    /**
     * Test whether the API returns a 404 status when an invoice is not found
     * 
     * @return void
     */
    public function test_should_return_404_if_invoice_not_found(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices/899');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data Invoice Tidak Ditemukan!'
                ]);
    }

    /**
     * Test whether the API successfully shows details of an invoice.
     * 
     * @return void
     */
    public function test_should_show_an_invoice_detail(): void
    {
        $invoice = Invoice::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/invoices/' . $invoice->id);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Detail Invoice!',
                    'data' => [
                        'id' => $invoice->id,
                        'invoice' => $invoice->invoice,
                    ]
                ]);
    }
}
