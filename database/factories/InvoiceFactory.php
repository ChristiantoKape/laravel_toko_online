<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Customer;
use App\Models\City;
use App\Models\Invoice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $customer = Customer::factory()->create();
        $city = City::factory()->create();

        return [
            'invoice' => 'INV-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'courier' => $this->faker->company,
            'courier_service' => $this->faker->randomElement(['Standard', 'Express', 'Overnight']),
            'courier_cost' => $this->faker->numberBetween(5000, 20000),
            'weight' => $this->faker->numberBetween(1, 100), // weight in kg
            'phone' => $this->faker->phoneNumber,
            'city_id' => $city->id,
            'province_id' => $city->province_id,
            'address' => $this->faker->address,
            'status' => $this->faker->randomElement(['pending', 'success', 'expired', 'failed']),
            'grand_total' => $this->faker->numberBetween(100000, 1000000),
            'snap_token' => Str::uuid(),
        ];
    }
}
