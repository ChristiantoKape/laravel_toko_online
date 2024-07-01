<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Cart;
use Tymon\JWTAuth\Facades\JWTAuth;

class CartWebControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment
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
     * Test that an unauthorized user cannot access the cart.
     *
     * @return void
     */
    public function test_unauthorized_user_cannot_access_cart()
    {
        $response = $this->getJson('api/web/carts');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test that an authorized user can access the cart.
     *
     * @return void
     */
    public function test_authorized_user_can_access_cart()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('api/web/carts');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Carts : '.$this->customer->name,
                    'data' => []
                ]);
    }

    /**
     * Test storing a product in the cart.
     * 
     * @return void
     */
    public function test_store_cart()
    {
        $product = Product::factory()->create();
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('api/web/carts', [
                            'product_id' => $product->id,
                            'qty' => 1,
                            'price' => $product->price,
                            'weight' => $product->weight
                        ]);
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Success Add To Cart'
                ]);

        // Test incrementing the quantity of an existing product in the cart
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('api/web/carts', [
                            'product_id' => $product->id,
                            'qty' => 1,
                            'price' => $product->price,
                            'weight' => $product->weight
                        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Success Add To Cart',
                 ]);
    }

    /**
     * Test getting the total price of items in the cart.
     *
     * @return void
     */
    public function test_get_cart_price()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('api/web/carts/total_price');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Total Cart Price',
                    'data' => 0
                ]);
    }
    
    /**
     * Test getting the total weight of items in the cart.
     *
     * @return void
     */
    public function test_get_cart_weight()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('api/web/carts/total_weight');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Total Cart Weight',
                    'data' => 0
                ]);
    }

    /**
     * Test that attempting to remove a non-existent cart returns a 404 status.
     *
     * @return void
     */
    public function test_remove_cart_not_found()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('api/web/carts/remove', [
                            'cart_id' => 999
                        ]);

        $response->assertStatus(404);
    }

    /**
     * Test successfully removing a cart.
     *
     * @return void
     */
    public function test_remove_cart_successfully()
    {
        $cart = Cart::factory()->create();
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('api/web/carts/remove', [
                            'cart_id' => $cart->id
                        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Success Remove Cart'
                ]);
    }
}
