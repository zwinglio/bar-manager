<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
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
            'restaurant_id' => Restaurant::factory(),
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'photo_path' => null,
            'price' => fake()->randomFloat(2, 5, 100),
            'cost' => fake()->randomFloat(2, 2, 50),
            'show_in_menu' => true,
            'available' => true,
            'sort_order' => 0,
        ];
    }
}
