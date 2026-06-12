<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'waiter_id' => Waiter::factory(),
            'number' => fake()->numberBetween(1, 50),
            'name' => fake()->optional()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'person_count' => fake()->numberBetween(1, 10),
            'opened_at' => now(),
            'closed_at' => null,
            'total' => null,
        ];
    }
}
