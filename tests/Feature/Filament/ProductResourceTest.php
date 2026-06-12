<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Resources\Products\Pages\CreateProduct;
use App\Filament\Restaurant\Resources\Products\Pages\EditProduct;
use App\Filament\Restaurant\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('restaurant');
    }

    public function test_list_page_renders(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(ListProducts::class)
            ->assertSuccessful();
    }

    public function test_can_create_product(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'product_category_id' => $category->id,
                'name' => 'Caipirinha',
                'description' => 'Classic Brazilian cocktail',
                'price' => 25.90,
                'cost' => 8.50,
                'show_in_menu' => true,
                'available' => true,
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Product::class, [
            'name' => 'Caipirinha',
            'restaurant_id' => $user->restaurant_id,
            'product_category_id' => $category->id,
        ]);
    }

    public function test_name_and_price_are_required_on_create(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => null,
                'price' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'price' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_product(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'product_category_id' => $category->id,
            'name' => 'Old Name',
        ]);

        Livewire::actingAs($user)
            ->test(EditProduct::class, ['record' => $product->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Product::class, [
            'id' => $product->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_product(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(EditProduct::class, ['record' => $product->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(Product::class, [
            'id' => $product->id,
        ]);
    }

    public function test_scoped_to_user_restaurant(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $userA = User::factory()->restaurant()->withRestaurant($restaurantA)->create();
        $productA = Product::factory()->create(['restaurant_id' => $restaurantA->id]);

        $restaurantB = Restaurant::factory()->create();
        $userB = User::factory()->restaurant()->withRestaurant($restaurantB)->create();
        $productB = Product::factory()->create(['restaurant_id' => $restaurantB->id]);

        Livewire::actingAs($userA)
            ->test(ListProducts::class)
            ->assertCanSeeTableRecords([$productA])
            ->assertCanNotSeeTableRecords([$productB]);
    }
}
