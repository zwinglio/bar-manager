<?php

namespace Tests\Feature\Waiter;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_mine_returns_only_own_tables(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $otherWaiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        $ownTable = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $otherWaiter->id,
            'closed_at' => null,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->get(route('waiter.tables.index', ['restaurant' => $restaurant->slug, 'scope' => 'mine']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tables/Index')
            ->has('tables', 1)
            ->where('tables.0.id', $ownTable->id)
        );
    }

    public function test_scope_all_returns_all_open_tables(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $otherWaiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);
        RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $otherWaiter->id,
            'closed_at' => null,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->get(route('waiter.tables.index', ['restaurant' => $restaurant->slug, 'scope' => 'all']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Tables/Index')
            ->has('tables', 2)
        );
    }

    public function test_closed_tables_are_excluded(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        RestaurantTable::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => now(),
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->get(route('waiter.tables.index', ['restaurant' => $restaurant->slug, 'scope' => 'all']));

        $response->assertInertia(fn ($page) => $page
            ->component('Tables/Index')
            ->has('tables', 0)
        );
    }

    public function test_cross_restaurant_tables_hidden(): void
    {
        $restaurant = Restaurant::factory()->create();
        $otherRestaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);

        RestaurantTable::factory()->create([
            'restaurant_id' => $otherRestaurant->id,
            'waiter_id' => $waiter->id,
            'closed_at' => null,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->get(route('waiter.tables.index', ['restaurant' => $restaurant->slug, 'scope' => 'all']));

        $response->assertInertia(fn ($page) => $page
            ->component('Tables/Index')
            ->has('tables', 0)
        );
    }
}
