<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Product;
use App\Models\Review;
use App\Models\Order;
use App\Models\Customer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->sentence,
            'product_id' => function() {
                return Product::factory()->create()->id;
            },
            'order_id' => function() {
                return Order::factory()->create()->id;
            },
            'customer_id' => function () {
                return Customer::factory()->create()->id;
            }
        ];
    }
}
