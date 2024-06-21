<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Slider;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;

class SliderControllerTest extends TestCase
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

        Storage::fake('local');
        $this->user = User::factory()->create();
        $this->token = JWTAuth::fromUser($this->user);
    }

    /**
     * Test whether the API endpoint returns a list of sliders
     * 
     * @return void
     */
    public function test_should_return_sliders_list(): void
    {
        Slider::factory()->count(5)->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/sliders');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'current_page',
                        'data' => [
                            '*' => ['id', 'image', 'link', 'created_at', 'updated_at'],
                        ],
                        'links'
                    ],
                ]);
    }

    /**
     * Test wheter the API endpoint returns an empty list when no sliders exist
     * 
     * @return void
     */
    public function test_should_return_empty_list_when_no_sliders_exist(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->getJson('/api/admin/sliders');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data retrieved successfully',
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
     * Test whether the API endpoint fails to store a slider due to validation errors.
     * 
     * @return void
     */
    public function test_should_fail_to_store_slider_due_to_validation_error(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->postJson('/api/admin/sliders', [
                            'image' => 'not_an_image'
                        ]);
        
        $response->assertStatus(422)
                ->assertJson([
                    'image' => [
                        'The image field must be an image.',
                        "The image field must be a file of type: jpeg, png, jpg, gif, svg."
                    ],
                ]);
    }

    /**
     * Test whether the API endpoint returns a "not found" response when deleting a non-existent slider.
     * 
     * @return void
     */
    public function test_should_return_not_found_when_deleting_non_existent_slider(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson('/api/admin/sliders/900');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Data Slider Tidak Ditemukan!',
                ]);
    }

    /**
     * Test whether the API endpoint successfully deletes a slider.
     * 
     * @return void
     */
    public function test_should_delete_slider_successfully(): void
    {
        $slider = Slider::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
                        ->deleteJson('/api/admin/sliders/' . $slider->id);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Data Slider Berhasil Dihapus!',
                ]);
        
        $this->assertDatabaseMissing('sliders', [
            'id' => $slider->id,
        ]);

        Storage::disk('local')->assertMissing('public/sliders/' . basename($slider->image));
    }
}
