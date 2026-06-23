<?php

namespace Tests\Feature\Filament;

use App\Enums\PaymentMethod;
use App\Filament\Restaurant\Resources\RestaurantTables\Pages\CreateRestaurantTable;
use App\Filament\Restaurant\Resources\RestaurantTables\Pages\EditRestaurantTable;
use App\Filament\Restaurant\Resources\RestaurantTables\Pages\ListRestaurantTables;
use App\Filament\Restaurant\Resources\RestaurantTables\RelationManagers\ProductsRelationManager;
use App\Filament\Restaurant\Resources\RestaurantTables\RestaurantTableResource;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\RestaurantTable;
use App\Models\RestaurantTableProduct;
use App\Models\User;
use App\Models\Waiter;
use Database\Seeders\RoleSeeder;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantTableResourceTest extends TestCase
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
            ->test(ListRestaurantTables::class)
            ->assertSuccessful();
    }

    public function test_can_create_table_with_products(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(CreateRestaurantTable::class)
            ->fillForm([
                'number' => 5,
                'name' => 'Mesa da Janela',
                'description' => 'Mesa com vista para a rua',
                'person_count' => 4,
                'waiter_id' => $waiter->id,
                'opened_at' => now()->toDateTimeLocalString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(RestaurantTable::class, [
            'number' => 5,
            'name' => 'Mesa da Janela',
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
    }

    public function test_can_add_product_via_relation_manager(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 12.50]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->callTableAction('create', data: [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => $product->price,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('product_restaurant_table', [
            'restaurant_table_id' => $table->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 12.50,
        ]);
    }

    public function test_duplicate_table_number_allowed(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(CreateRestaurantTable::class)
            ->fillForm([
                'number' => 10,
                'waiter_id' => $waiter->id,
                'opened_at' => now()->toDateTimeLocalString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        Livewire::actingAs($user)
            ->test(CreateRestaurantTable::class)
            ->fillForm([
                'number' => 10,
                'waiter_id' => $waiter->id,
                'opened_at' => now()->toDateTimeLocalString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseCount(RestaurantTable::class, 2);
    }

    public function test_current_total_computed_correctly(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $productA = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 10.00]);
        $productB = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 5.50]);

        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);

        $table->products()->attach($productA->id, ['quantity' => 2, 'unit_price' => 10.00]);
        $table->products()->attach($productB->id, ['quantity' => 3, 'unit_price' => 5.50]);

        $expected = (2 * 10.00) + (3 * 5.50);
        $this->assertEquals($expected, (float) $table->current_total);
    }

    public function test_close_action_freezes_total(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 20.00]);

        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 3, 'unit_price' => 20.00]);

        $this->assertTrue($table->isOpen());

        $table->close(PaymentMethod::Pix);
        $table->refresh();

        $this->assertNotNull($table->closed_at);
        $this->assertEquals(60.00, (float) $table->total);

        $product->update(['price' => 99.99]);
        $table->refresh();

        $this->assertEquals(60.00, (float) $table->total);
    }

    public function test_validation_on_create(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateRestaurantTable::class)
            ->fillForm([
                'number' => null,
                'waiter_id' => null,
                'opened_at' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'number' => 'required',
                'opened_at' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_table(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
            'number' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->fillForm([
                'number' => 99,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(RestaurantTable::class, [
            'id' => $table->id,
            'number' => 99,
        ]);
    }

    public function test_can_delete_table(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(RestaurantTable::class, [
            'id' => $table->id,
        ]);
    }

    public function test_can_close_table_from_list_page(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 15.00]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 2, 'unit_price' => 15.00]);

        $this->assertTrue($table->isOpen());

        Livewire::actingAs($user)
            ->test(ListRestaurantTables::class)
            ->callAction(TestAction::make('close')->table($table), data: [
                'payment_method' => PaymentMethod::Pix->value,
            ])
            ->assertNotified();

        $table->refresh();

        $this->assertNotNull($table->closed_at);
        $this->assertEquals(30.00, (float) $table->total);
        $this->assertEquals(PaymentMethod::Pix, $table->payment_method);
    }

    public function test_scoped_to_user_restaurant(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $userA = User::factory()->restaurant()->withRestaurant($restaurantA)->create();
        $waiterA = Waiter::factory()->create(['restaurant_id' => $restaurantA->id]);
        $tableA = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurantA->id,
            'waiter_id' => $waiterA->id,
        ]);

        $restaurantB = Restaurant::factory()->create();
        $userB = User::factory()->restaurant()->withRestaurant($restaurantB)->create();
        $waiterB = Waiter::factory()->create(['restaurant_id' => $restaurantB->id]);
        $tableB = RestaurantTable::factory()->create([
            'restaurant_id' => $restaurantB->id,
            'waiter_id' => $waiterB->id,
        ]);

        Livewire::actingAs($userA)
            ->test(ListRestaurantTables::class)
            ->assertCanSeeTableRecords([$tableA])
            ->assertCanNotSeeTableRecords([$tableB]);
    }

    public function test_payment_method_column_displays_after_close(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->close(PaymentMethod::Pix);
        $table->refresh();

        Livewire::actingAs($user)
            ->test(ListRestaurantTables::class)
            ->assertCanSeeTableRecords([$table])
            ->assertSee(PaymentMethod::Pix->label());
    }

    public function test_filter_by_payment_method(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);

        $pixTable = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $pixTable->close(PaymentMethod::Pix);

        $cashTable = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $cashTable->close(PaymentMethod::Cash);

        Livewire::actingAs($user)
            ->test(ListRestaurantTables::class)
            ->filterTable('payment_method', PaymentMethod::Pix->value)
            ->assertCanSeeTableRecords([$pixTable->refresh()])
            ->assertCanNotSeeTableRecords([$cashTable->refresh()]);
    }

    public function test_products_relation_manager_renders_on_edit_page(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 2, 'unit_price' => $product->price]);
        $pivot = RestaurantTableProduct::where('restaurant_table_id', $table->id)->first();

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->assertSeeLivewire(ProductsRelationManager::class);

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->assertOk()
            ->assertCanSeeTableRecords([$pivot]);
    }

    public function test_increment_action_increases_quantity(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 2, 'unit_price' => $product->price]);
        $pivot = RestaurantTableProduct::where('restaurant_table_id', $table->id)->first();

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->callTableAction('increment', $pivot);

        $this->assertSame(3, (int) $pivot->refresh()->quantity);
    }

    public function test_decrement_action_decreases_quantity(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 3, 'unit_price' => $product->price]);
        $pivot = RestaurantTableProduct::where('restaurant_table_id', $table->id)->first();

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->callTableAction('decrement', $pivot);

        $this->assertSame(2, (int) $pivot->refresh()->quantity);
    }

    public function test_decrement_action_deletes_row_at_zero(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 1, 'unit_price' => $product->price]);
        $pivot = RestaurantTableProduct::where('restaurant_table_id', $table->id)->first();

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->callTableAction('decrement', $pivot);

        $this->assertDatabaseMissing('product_restaurant_table', [
            'id' => $pivot->id,
        ]);
    }

    public function test_remove_action_deletes_row(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 5, 'unit_price' => $product->price]);
        $pivot = RestaurantTableProduct::where('restaurant_table_id', $table->id)->first();

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $table,
                'pageClass' => EditRestaurantTable::class,
            ])
            ->callTableAction('remove', $pivot);

        $this->assertDatabaseMissing('product_restaurant_table', [
            'id' => $pivot->id,
        ]);
    }

    public function test_can_close_table_from_edit_page(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $product = Product::factory()->create(['restaurant_id' => $user->restaurant_id, 'price' => 15.00]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->products()->attach($product->id, ['quantity' => 2, 'unit_price' => 15.00]);

        $this->assertTrue($table->isOpen());

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->callAction('close', data: [
                'payment_method' => PaymentMethod::Cash->value,
            ])
            ->assertNotified();

        $table->refresh();

        $this->assertNotNull($table->closed_at);
        $this->assertEquals(30.00, (float) $table->total);
        $this->assertEquals(PaymentMethod::Cash, $table->payment_method);
    }

    public function test_close_action_hidden_when_table_already_closed(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
        ]);
        $table->close(PaymentMethod::Pix);

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->assertActionHidden('close');
    }

    public function test_edit_save_redirects_to_list(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);
        $table = RestaurantTable::factory()->create([
            'restaurant_id' => $user->restaurant_id,
            'waiter_id' => $waiter->id,
            'number' => 1,
        ]);

        Livewire::actingAs($user)
            ->test(EditRestaurantTable::class, ['record' => $table->id])
            ->fillForm(['number' => 77])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified()
            ->assertRedirect(RestaurantTableResource::getUrl('index'));

        $this->assertDatabaseHas(RestaurantTable::class, [
            'id' => $table->id,
            'number' => 77,
        ]);
    }
}
