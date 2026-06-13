<?php

namespace Tests\Feature\Waiter;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloseTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_close_open_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.close', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]));

        $response->assertRedirect(route('waiter.tables.index', ['restaurant' => $restaurant->slug]));

        $table->refresh();
        $this->assertNotNull($table->closed_at);
        $this->assertNotNull($table->total);
    }

    public function test_closing_already_closed_table_redirects_without_error(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => now(),
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.close', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]));

        $response->assertRedirect(route('waiter.tables.show', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]));
    }

    public function test_guest_cannot_close_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'closed_at' => null,
        ]);

        $response = $this->post(route('waiter.tables.close', [
            'restaurant' => $restaurant->slug,
            'table' => $table->id,
        ]));

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurant->slug]));
    }
}
