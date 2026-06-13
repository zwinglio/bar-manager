<?php

namespace Tests\Feature\Waiter;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_open_table(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.store', ['restaurant' => $restaurant->slug]), [
            'number' => 5,
            'person_count' => 4,
            'name' => 'Birthday',
            'description' => 'Window seat',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(RestaurantTable::class, [
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'number' => 5,
            'person_count' => 4,
            'name' => 'Birthday',
            'description' => 'Window seat',
        ]);

        $table = RestaurantTable::where('number', 5)->first();
        $this->assertNotNull($table->opened_at);
    }

    public function test_default_person_count_is_one(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.tables.store', ['restaurant' => $restaurant->slug]), [
            'number' => 1,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas(RestaurantTable::class, [
            'number' => 1,
            'person_count' => 1,
        ]);
    }

    public function test_guest_cannot_open_table(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('waiter.tables.store', ['restaurant' => $restaurant->slug]), [
            'number' => 1,
        ]);

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurant->slug]));
    }
}
