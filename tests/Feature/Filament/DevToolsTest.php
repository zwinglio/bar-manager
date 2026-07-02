<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Pages\DevTools;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Waiter;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DevToolsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('restaurant');
    }

    public function test_dev_tools_page_renders_in_local_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(DevTools::class)
            ->assertSuccessful();
    }

    public function test_dev_tools_page_is_forbidden_in_production_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(DevTools::class)
            ->assertForbidden();
    }

    public function test_dev_tools_route_is_forbidden_in_production_environment(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $user = User::factory()->restaurant()->withRestaurant()->create();

        $this->actingAs($user)
            ->get('/restaurant/dev-tools')
            ->assertForbidden();
    }

    public function test_seed_mock_data_action_creates_categories_products_and_waiter(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $user = User::factory()->restaurant()->withRestaurant()->create();
        $restaurant = $user->restaurant;

        Livewire::actingAs($user)
            ->test(DevTools::class)
            ->callAction('seedMockData')
            ->assertNotified();

        $this->assertSame(6, ProductCategory::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(13, Product::where('restaurant_id', $restaurant->id)->count());

        $waiter = Waiter::where('restaurant_id', $restaurant->id)->where('username', 'garcom.teste')->first();
        $this->assertNotNull($waiter);
        $this->assertSame('Garçom Teste', $waiter->name);
        $this->assertTrue($waiter->is_active);
    }

    public function test_seed_mock_data_action_is_idempotent(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $user = User::factory()->restaurant()->withRestaurant()->create();
        $restaurant = $user->restaurant;

        $component = Livewire::actingAs($user)->test(DevTools::class);

        $component->callAction('seedMockData')->assertNotified();
        $component->callAction('seedMockData')->assertNotified();

        $this->assertSame(6, ProductCategory::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(13, Product::where('restaurant_id', $restaurant->id)->count());
        $this->assertSame(
            1,
            Waiter::where('restaurant_id', $restaurant->id)->where('username', 'garcom.teste')->count(),
        );
    }
}
