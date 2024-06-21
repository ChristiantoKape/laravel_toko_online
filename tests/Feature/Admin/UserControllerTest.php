<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment before each test method.
     * 
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@testemail.com',
        ]);
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test that the API fails to return a list of users when unauthenticated.
     * 
     * @return void
     */
    public function test_should_fail_to_return_list_of_users_when_unauthenticated(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    /**
     * Test that the API returns an empty list when no users exist.
     * 
     * @return void
     */
    public function test_should_return_a_list_when_no_users_exists(): void
    {
        User::where('email', '!=', 'admin@testemail.com')->delete();

        // make request to index endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                     ->getJson('/api/admin/users');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Users',
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
     * Test that the API returns a list of users without a search query.
     *
     * @return void
     */
    public function test_should_return_a_list_without_search_query(): void
    {
        User::factory()->count(10)->create();

        // make request to index endpoint
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/users');

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
     * Test that the API returns a list of users with a search query.
     *
     * @return void
     */
    public function test_should_return_a_list_with_search_query(): void
    {
        User::factory()->create(['name' => 'Jonathan Liandi']);
        User::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/users?search=Jonathan');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data Users',
                    'data' => [
                        'data' => [
                            [
                                'name' => 'Jonathan Liandi'
                            ]
                        ],
                        'current_page' => 1,
                        'last_page' => 2,
                        'per_page' => 5,
                        'total' => 6
                    ]
                ]);
    }

    /**
     * Test that the API fails to store a user with invalid data.
     * 
     * @return void
     */
    public function test_should_fail_to_store_user_with_invalid_data(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/admin/users', []);

        $response->assertStatus(422)
                ->assertJson([
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ]);
    }

    /**
     * Test that the API successfully stores a user.
     *
     * @return void
     */
    public function test_should_store_user_successfully(): void
    {
        $dataUser = [
            'name' => 'setiawan ade',
            'email' => 'set1awanade@gmail.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/admin/users', $dataUser);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data User Berhasil Ditambahkan!',
                    'data' => [
                        'name' => 'setiawan ade',
                        'email' => 'set1awanade@gmail.com',
                    ]
                ]);
    }

    /**
     * Test that the API fails to show a non-existent user.
     *
     * @return void
     */
    public function test_should_fail_to_show_non_existent_user(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/users/100');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Detail Data User Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that the API shows user details.
     *
     * @return void
     */
    public function test_should_show_user_details(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/users/' . $user->id);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Detail Data User',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]);
    }

    /**
     * Test that the API returns a 404 status for a non-existent user during update
     * 
     * @return void
     */
    public function test_should_return_404_for_nonexistent_user_on_update(): void
    {
        $updateUser = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => ''
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson('/api/admin/users/901', $updateUser);

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data User Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that the API successfully updates a user.
     * 
     * @return void
     */
    public function test_should_update_user_successfully(): void
    {
        $user = User::factory()->create();

        $updateUser = [
            'name' => 'setiawan ade',
            'email' => 'set1awanadejago@gmail.com',
        ];

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->putJson('/api/admin/users/' . $user->id, $updateUser);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data User Berhasil Diupdate!',
                ]);

        $responseData = $response->json('data');

        $this->assertEquals($updateUser['name'], $responseData['name']);
        $this->assertEquals($updateUser['email'], $responseData['email']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $updateUser['name'],
            'email' => $updateUser['email'],
        ]);
    }

    /**
     * Test that the API returns a 404 status for a non-existent user during deletion.
     *
     * @return void
     */
    public function test_should_return_404_for_nonexistent_user_on_delete(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson('/api/admin/users/901');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data User Tidak Ditemukan!',
                ]);
    }

    /**
     * Test that the API successfully deletes a user.
     *
     * @return void
     */
    public function test_should_destroy_product_successfully():void
    {
        $user = User::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson('/api/admin/users/' . $user->id);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data User Berhasil Dihapus!',
                ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }
}