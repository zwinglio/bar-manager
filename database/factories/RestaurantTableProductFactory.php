<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\RestaurantTableProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTableProduct>
 */
class RestaurantTableProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_table_id' => RestaurantTable::factory(),
            'product_id' => Product::factory(),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 5, 100),
        ];
    }
}
