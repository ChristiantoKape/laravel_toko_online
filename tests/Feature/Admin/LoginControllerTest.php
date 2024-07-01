<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Models\User;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login with valid credentials
     * 
     * @return void
     */
    public function test_login_valid_credentials(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password')
        ]);

        // call the login route
        $response = $this->postJson('/api/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        // check the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'user',
                    'token'
                ]);
    }

    /**
     * Test login with invalid credentials
     * 
     * @return void
     */
    public function test_login_invalid_credentials(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Call the login route with incorrect password
        $response = $this->postJson('/api/admin/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword'
        ]);

        // Check the response
        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Email or Password is incorrect'
                ]);
    }

    /**
     * Test login validation failure
     * 
     * @return void
     */
    public function test_login_validation_failure(): void
    {
        // Call the login route without email and password
        $response = $this->postJson('/api/admin/login', []);

        // Check the response
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'email',
                    'password'
                ]);
    }

    /**
     * Test retrieving the authenticated user
     * 
     * @return void
     */
    public function test_getUser(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Simulate login
        $token = JWTAuth::fromUser($user);

        // Call the getUser route
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/admin/user');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'user' => $user->toArray()
                ]);
    }

    /**
     * Test refreshing the authentication token
     * 
     * @return void
     */
    public function test_refresh_token(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Simulate login
        $token = JWTAuth::fromUser($user);

        // Call the refreshToken route
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/admin/refresh');

        // check the response
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'user',
                    'token'
                ]);

        // check if the token is different
        $newToken = $response->json('token');
        $this->assertNotEquals($token, $newToken);
    }

    /**
     * Test logging out
     * 
     * @return void
     */
    public function test_logout(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Simulate login
        $token = JWTAuth::fromUser($user);

        // Call the logout route
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/admin/logout');

        // Check the response
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }
}
