<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class RegisterCustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that registration fails when required fields (name, email, password) are missing.
     * 
     * @return void
     */
    public function test_fails_registration_with_missing_fields()
    {
        $response = $this->postJson('/api/customer/register', []);

        $response->assertStatus(422)
                ->assertJson([
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ]);
    }

    /**
     * Test that registration fails when an invalid email address is provided.
     * 
     * @return void
     */
    public function test_fails_registration_with_invalid_email()
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => bcrypt('password'),
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'email' => ['The email field must be a valid email address.'],
                ]);
    }

    /**
     * Test that registration fails when an existing email address is used.
     * 
     * @return void
     */
    public function test_fails_registration_with_existing_email()
    {
        Customer::factory()->create([
            'name' => 'Jane Doe',
            'email' => 'johnatan_doe@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/customer/register', [
            'name' => 'John Doe',
            'email' => 'johnatan_doe@gmail.com',
            'password' => bcrypt('password'),
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'email' => ['The email has already been taken.'],
                ]);
    }

    /**
     * Test that registration fails when password confirmation does not match.
     * 
     * @return void
     */
    public function test_fails_registration_with_password_confirmation_missmatch()
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'John Doe',
            'email' => 'johnatan_doe@gmail.com',
            'password' => bcrypt('password'),
            'password_confirmation' => bcrypt('password_confirmation'),
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'password' => ['The password field confirmation does not match.'],
                ]);
    }

    /**
     * Test successful registration with valid input
     * 
     * @return void
     */
    public function test_registers_a_customer_with_valid_input()
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Register Customer Berhasil',
                 ]);

        $this->assertDatabaseHas('customers', [
            'email' => 'johndoe@example.com',
        ]);

        $customer = Customer::where('email', 'johndoe@example.com')->first();
        $this->assertTrue(Hash::check('password', $customer->password));
    }
}
