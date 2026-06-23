<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Widgets\ProductsSoldChart;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Models\Waiter;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsSoldChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('restaurant');
    }

    public function test_chart_renders(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);

        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
            'opened_at' => now(),
        ]);
        $table->products()->attach($product->id, ['quantity' => 5, 'unit_price' => $product->price]);

        Livewire::actingAs($user)
            ->test(ProductsSoldChart::class)
            ->assertOk()
            ->assertSee('Produtos vendidos (30 dias): 5');
    }

    public function test_chart_total_reflects_last_thirty_days_only(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);

        $recentTable = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
            'opened_at' => now()->subDays(2),
        ]);
        $recentTable->products()->attach($product->id, ['quantity' => 4, 'unit_price' => $product->price]);

        $oldTable = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
            'opened_at' => now()->subDays(60),
        ]);
        $oldTable->products()->attach($product->id, ['quantity' => 100, 'unit_price' => $product->price]);

        Livewire::actingAs($user)
            ->test(ProductsSoldChart::class)
            ->assertSee('Produtos vendidos (30 dias): 4');
    }

    public function test_chart_is_scoped_to_user_restaurant(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $userA = User::factory()->restaurant()->withRestaurant($restaurantA)->create();
        $waiterA = Waiter::factory()->create(['restaurant_id' => $restaurantA->id]);
        $productA = Product::factory()->create(['restaurant_id' => $restaurantA->id]);

        $tableA = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurantA->id,
            'waiter_id' => $waiterA->id,
            'opened_at' => now(),
        ]);
        $tableA->products()->attach($productA->id, ['quantity' => 7, 'unit_price' => $productA->price]);

        $restaurantB = Restaurant::factory()->create();
        $userB = User::factory()->restaurant()->withRestaurant($restaurantB)->create();
        $waiterB = Waiter::factory()->create(['restaurant_id' => $restaurantB->id]);
        $productB = Product::factory()->create(['restaurant_id' => $restaurantB->id]);

        $tableB = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurantB->id,
            'waiter_id' => $waiterB->id,
            'opened_at' => now(),
        ]);
        $tableB->products()->attach($productB->id, ['quantity' => 50, 'unit_price' => $productB->price]);

        Livewire::actingAs($userA)
            ->test(ProductsSoldChart::class)
            ->assertSee('Produtos vendidos (30 dias): 7');

        Livewire::actingAs($userB)
            ->test(ProductsSoldChart::class)
            ->assertSee('Produtos vendidos (30 dias): 50');
    }
}
