<?php

namespace Tests\Feature\Web;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Slider;

class SliderWebControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the index method.
     * 
     * @return void
     */
    public function test_index()
    {
        Slider::factory()->count(5)->create();

        $response = $this->getJson('/api/web/sliders');

        // dd($response->dump());
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'List Data SLiders',
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        '*' => [
                            'id',
                            'image',
                            'link',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ]);
    }
}
