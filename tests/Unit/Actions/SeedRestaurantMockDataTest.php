<?php

namespace Tests\Unit\Actions;

use App\Actions\Development\SeedRestaurantMockData;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeedRestaurantMockDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_categories_products_and_waiter(): void
    {
        $restaurant = Restaurant::factory()->create();

        $result = (new SeedRestaurantMockData)($restaurant);

        $this->assertSame(6, $result['categories']);
        $this->assertSame(13, $result['products']);
        $this->assertTrue($result['waiter_created']);

        $this->assertSame(6, ProductCategory::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(13, Product::where('restaurant_id', $restaurant->id)->count());

        $waiter = Waiter::where('restaurant_id', $restaurant->id)->where('username', 'garcom.teste')->first();
        $this->assertNotNull($waiter);
        $this->assertSame('Garçom Teste', $waiter->name);
        $this->assertTrue($waiter->is_active);
    }

    public function test_is_idempotent(): void
    {
        $restaurant = Restaurant::factory()->create();
        $seeder = new SeedRestaurantMockData;

        $first = $seeder($restaurant);
        $second = $seeder($restaurant);

        $this->assertSame(6, $first['categories']);
        $this->assertSame(13, $first['products']);
        $this->assertTrue($first['waiter_created']);

        $this->assertSame(0, $second['categories']);
        $this->assertSame(0, $second['products']);
        $this->assertFalse($second['waiter_created']);

        $this->assertSame(6, ProductCategory::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(13, Product::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(
            1,
            Waiter::where('restaurant_id', $restaurant->id)->where('username', 'garcom.teste')->count(),
        );
    }

    public function test_does_not_leak_data_between_restaurants(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $restaurantB = Restaurant::factory()->create();

        (new SeedRestaurantMockData)($restaurantA);

        $this->assertSame(0, ProductCategory::where('restaurant_id', $restaurantB->id)->count());
        $this->assertSame(0, Product::where('restaurant_id', $restaurantB->id)->count());
        $this->assertSame(0, Waiter::where('restaurant_id', $restaurantB->id)->count());
    }

    public function test_seeded_products_are_available_and_visible_in_menu(): void
    {
        $restaurant = Restaurant::factory()->create();

        (new SeedRestaurantMockData)($restaurant);

        $products = Product::where('restaurant_id', $restaurant->id)->get();

        $this->assertTrue($products->every(fn (Product $product): bool => $product->available));
        $this->assertTrue($products->every(fn (Product $product): bool => $product->show_in_menu));
        $this->assertTrue($products->every(fn (Product $product): bool => $product->product_category_id !== null));
    }
}
