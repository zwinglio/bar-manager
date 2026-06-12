<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Restaurants\Pages\CreateRestaurant;
use App\Filament\Resources\Restaurants\Pages\EditRestaurant;
use App\Filament\Resources\Restaurants\Pages\ListRestaurants;
use App\Models\Restaurant;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_page_renders(): void
    {
        $user = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(ListRestaurants::class)
            ->assertSuccessful();
    }

    public function test_can_create_restaurant_with_new_admin(): void
    {
        $user = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateRestaurant::class)
            ->fillForm([
                'name' => 'Test Restaurant',
                'slug' => 'test-restaurant',
                'address' => '123 Main St',
                'phone' => '555-1234',
                'email' => 'test@example.com',
                'is_active' => true,
                'admin_mode' => 'new',
                'admin_name' => 'Admin User',
                'admin_email' => 'admin@example.com',
                'admin_password' => 'secret123',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Restaurant::class, [
            'name' => 'Test Restaurant',
            'slug' => 'test-restaurant',
        ]);

        $restaurant = Restaurant::where('slug', 'test-restaurant')->first();
        $this->assertDatabaseHas(User::class, [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'restaurant_id' => $restaurant->id,
        ]);

        $admin = User::where('email', 'admin@example.com')->first();
        $this->assertTrue($admin->hasRole('restaurant'));
        $this->assertTrue(Hash::check('secret123', $admin->password));
    }

    public function test_can_create_restaurant_with_existing_admin(): void
    {
        $user = User::factory()->restaurant()->create();
        $existingAdmin = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateRestaurant::class)
            ->fillForm([
                'name' => 'Test Restaurant',
                'slug' => 'test-restaurant',
                'admin_mode' => 'existing',
                'admin_user_id' => $existingAdmin->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $restaurant = Restaurant::where('slug', 'test-restaurant')->first();
        $this->assertEquals($existingAdmin->id, $restaurant->admin->id);
    }

    public function test_name_is_required_on_create(): void
    {
        $user = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateRestaurant::class)
            ->fillForm([
                'name' => null,
                'slug' => 'test-restaurant',
                'admin_mode' => 'new',
                'admin_name' => 'Admin',
                'admin_email' => 'admin@test.com',
                'admin_password' => 'password123',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_restaurant(): void
    {
        $user = User::factory()->restaurant()->create();
        $restaurant = Restaurant::factory()->create(['name' => 'Old Name']);
        $admin = User::factory()->restaurant()->create();
        $admin->update(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(EditRestaurant::class, ['record' => $restaurant->id])
            ->fillForm([
                'name' => 'Updated Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Restaurant::class, [
            'id' => $restaurant->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_edit_restaurant_and_reassign_admin(): void
    {
        $user = User::factory()->restaurant()->create();
        $restaurant = Restaurant::factory()->create();
        $oldAdmin = User::factory()->restaurant()->create();
        $oldAdmin->update(['restaurant_id' => $restaurant->id]);
        $newAdmin = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(EditRestaurant::class, ['record' => $restaurant->id])
            ->fillForm([
                'admin_mode' => 'existing',
                'admin_user_id' => $newAdmin->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(User::class, [
            'id' => $newAdmin->id,
            'restaurant_id' => $restaurant->id,
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $oldAdmin->id,
            'restaurant_id' => null,
        ]);
    }

    public function test_can_edit_restaurant_and_create_new_admin(): void
    {
        $user = User::factory()->restaurant()->create();
        $restaurant = Restaurant::factory()->create();
        $oldAdmin = User::factory()->restaurant()->create();
        $oldAdmin->update(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(EditRestaurant::class, ['record' => $restaurant->id])
            ->fillForm([
                'admin_mode' => 'new',
                'admin_name' => 'New Admin',
                'admin_email' => 'newadmin@example.com',
                'admin_password' => 'secret123',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(User::class, [
            'name' => 'New Admin',
            'email' => 'newadmin@example.com',
            'restaurant_id' => $restaurant->id,
        ]);

        $this->assertDatabaseHas(User::class, [
            'id' => $oldAdmin->id,
            'restaurant_id' => null,
        ]);
    }

    public function test_can_delete_restaurant(): void
    {
        $user = User::factory()->restaurant()->create();
        $restaurant = Restaurant::factory()->create();

        Livewire::actingAs($user)
            ->test(EditRestaurant::class, ['record' => $restaurant->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(Restaurant::class, [
            'id' => $restaurant->id,
        ]);
    }
}
