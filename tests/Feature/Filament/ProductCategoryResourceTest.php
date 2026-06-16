<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Resources\ProductCategories\Pages\CreateProductCategory;
use App\Filament\Restaurant\Resources\ProductCategories\Pages\EditProductCategory;
use App\Filament\Restaurant\Resources\ProductCategories\Pages\ListProductCategories;
use App\Filament\Restaurant\Resources\ProductCategories\RelationManagers\ProductsRelationManager;
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

class ProductCategoryResourceTest extends TestCase
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
            ->test(ListProductCategories::class)
            ->assertSuccessful();
    }

    public function test_can_create_product_category(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateProductCategory::class)
            ->fillForm([
                'name' => 'Drinks',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(ProductCategory::class, [
            'name' => 'Drinks',
            'restaurant_id' => $user->restaurant_id,
        ]);
    }

    public function test_name_is_required_on_create(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateProductCategory::class)
            ->fillForm([
                'name' => null,
                'sort_order' => 0,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_product_category(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $user->restaurant_id, 'name' => 'Old Name']);

        Livewire::actingAs($user)
            ->test(EditProductCategory::class, ['record' => $category->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(ProductCategory::class, [
            'id' => $category->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_product_category(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(EditProductCategory::class, ['record' => $category->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(ProductCategory::class, [
            'id' => $category->id,
        ]);
    }

    public function test_scoped_to_user_restaurant(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $userA = User::factory()->restaurant()->withRestaurant($restaurantA)->create();
        $categoryA = ProductCategory::factory()->create(['restaurant_id' => $restaurantA->id]);

        $restaurantB = Restaurant::factory()->create();
        $userB = User::factory()->restaurant()->withRestaurant($restaurantB)->create();
        $categoryB = ProductCategory::factory()->create(['restaurant_id' => $restaurantB->id]);

        Livewire::actingAs($userA)
            ->test(ListProductCategories::class)
            ->assertCanSeeTableRecords([$categoryA])
            ->assertCanNotSeeTableRecords([$categoryB]);
    }

    public function test_create_redirects_to_index(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateProductCategory::class)
            ->fillForm([
                'name' => 'New Category',
                'sort_order' => 1,
            ])
            ->call('create')
            ->assertRedirect(ListProductCategories::class);
    }

    public function test_products_relation_manager_renders(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $category = ProductCategory::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'product_category_id' => $category->id,
            'name' => 'Category Product',
        ]);

        Livewire::actingAs($user)
            ->test(EditProductCategory::class, ['record' => $category->id])
            ->assertSeeLivewire(ProductsRelationManager::class);

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $category,
                'pageClass' => EditProductCategory::class,
            ])
            ->assertOk()
            ->assertCanSeeTableRecords([$product]);
    }
}
