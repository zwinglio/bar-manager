<?php

namespace Tests\Feature\Waiter;

use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\RestaurantTableProduct;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_add_new_product_to_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        $product = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'price' => 25.50,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.products.store', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(RestaurantTableProduct::class, [
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 25.50,
        ]);
    }

    public function test_adding_existing_product_increments_quantity(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        $product = Product::factory()->create([
            'restaurant_id' => $restaurant->id,
            'price' => 10.00,
        ]);
        RestaurantTableProduct::factory()->create([
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.products.store', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]), [
            'product_id' => $product->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(RestaurantTableProduct::class, [
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
    }

    public function test_can_update_quantity(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        $product = Product::factory()->create(['restaurant_id' => $restaurant->id]);
        $pivot = RestaurantTableProduct::factory()->create([
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->patch(route('waiter.tables.products.update', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
            'product' => $pivot->id,
        ]), [
            'quantity' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas(RestaurantTableProduct::class, [
            'id' => $pivot->id,
            'quantity' => 5,
        ]);
    }

    public function test_cannot_update_quantity_below_one(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        $product = Product::factory()->create(['restaurant_id' => $restaurant->id]);
        $pivot = RestaurantTableProduct::factory()->create([
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->patch(route('waiter.tables.products.update', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
            'product' => $pivot->id,
        ]), [
            'quantity' => 0,
        ]);

        $response->assertSessionHasErrors(['quantity']);
    }

    public function test_can_delete_product_row(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        $product = Product::factory()->create(['restaurant_id' => $restaurant->id]);
        $pivot = RestaurantTableProduct::factory()->create([
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->delete(route('waiter.tables.products.destroy', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
            'product' => $pivot->id,
        ]));

        $response->assertRedirect();
        $this->assertDatabaseMissing(RestaurantTableProduct::class, [
            'id' => $pivot->id,
        ]);
    }

    public function test_cannot_mutate_closed_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => now(),
        ]);
        $product = Product::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->actingAs($waiter, 'waiter');

        $this->post(route('waiter.tables.products.store', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]), [
            'product_id' => $product->id,
        ])->assertRedirect();

        $this->assertDatabaseMissing(RestaurantTableProduct::class, [
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
        ]);
    }
}
