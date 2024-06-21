<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class DashboardControllerTest extends TestCase
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
     * Test that the API returns a successful response for the dashboard
     * 
     * @return void
     */
    public function test_should_return_successful_response(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that the API returns the correct JSON structure for the dashboard.
     * 
     * @return void
     */
    public function test_should_returns_correct_structure(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'count' => [
                            'pending',
                            'success',
                            'expired',
                            'failed'
                        ],
                        'chart' => [
                            'month_name',
                            'grand_total'
                        ]
                    ]
                ]);
    }

    /**
     * Test that the API returns the correct counts and chart data for the dashboard.
     * 
     * @return void
     */
    public function test_should_returns_correct_counts_and_chart_data(): void
    {
        $currentYear = date('Y');

        $months = [];
        $grandTotals = [];

        // membuat invoice dengan berbagai status dan menyimpan data yang relevan
        Invoice::factory()->count(5)->create(['status' => 'pending']);
        $successInvoices = Invoice::factory()->count(4)->create(['status' => 'success']);
        Invoice::factory()->count(2)->create(['status' => 'expired']);
        Invoice::factory()->count(1)->create(['status' => 'failed']);

        foreach ($successInvoices as $invoice) {
            $month = $invoice->created_at->format('F'); // Mengambil nama bulan
            $total = $invoice->grand_total;
    
            // Menyimpan data ke array
            if (isset($months[$month])) {
                $months[$month] += $total;
            } else {
                $months[$month] = $total;
            }
        }

        // memisahkan nama bulan dan total besar dari array
        $expectedMonthNames = array_keys($months);
        $expectedGrandTotals = array_values($months);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                         ->getJson('/api/admin/dashboard');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Statistik Data',
                    'data' => [
                        'count' => [
                            'pending' => 5,
                            'success' => 4,
                            'expired' => 2,
                            'failed' => 1
                        ],
                        'chart' => [
                            'month_name' => $expectedMonthNames,
                            'grand_total' => $expectedGrandTotals
                        ]
                    ]
                ]);
    }
}
