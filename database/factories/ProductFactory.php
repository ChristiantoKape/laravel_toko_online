<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Category;
use App\Models\User;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => function() {
                return Category::factory()->create()->id;
            },
            'user_id' => function() {
                return User::factory()->create()->id;
            },
            'image' => 'products/' . $this->faker->image('public/storage/products', 640, 480, null, false),
            'title' => $this->faker->name,
            'slug' => \Str::slug($this->faker->name),
            'description' => $this->faker->text,
            'weight' => $this->faker->randomNumber(3),
            'price' => $this->faker->randomNumber(5),
            'stock' => $this->faker->randomNumber(2),
            'discount' => $this->faker->randomNumber(2),
        ];
    }
}
