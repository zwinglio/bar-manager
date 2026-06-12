<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Resources\Waiters\Pages\CreateWaiter;
use App\Filament\Restaurant\Resources\Waiters\Pages\EditWaiter;
use App\Filament\Restaurant\Resources\Waiters\Pages\ListWaiters;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Waiter;
use Database\Seeders\RoleSeeder;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class WaiterResourceTest extends TestCase
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
            ->test(ListWaiters::class)
            ->assertSuccessful();
    }

    public function test_can_create_waiter(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateWaiter::class)
            ->fillForm([
                'name' => 'João Silva',
                'username' => 'joao',
                'password' => 'secret123',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Waiter::class, [
            'name' => 'João Silva',
            'username' => 'joao',
            'restaurant_id' => $user->restaurant_id,
        ]);

        $this->assertTrue(
            Hash::check('secret123', Waiter::where('username', 'joao')->first()->password)
        );
    }

    public function test_name_and_username_are_required_on_create(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateWaiter::class)
            ->fillForm([
                'name' => null,
                'username' => null,
                'password' => 'secret123',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'username' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_password_is_required_on_create(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(CreateWaiter::class)
            ->fillForm([
                'name' => 'João Silva',
                'username' => 'joao',
                'password' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'password' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_waiter_without_changing_password(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id, 'name' => 'Old Name']);

        Livewire::actingAs($user)
            ->test(EditWaiter::class, ['record' => $waiter->id])
            ->fillForm([
                'name' => 'Updated Name',
                'password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Waiter::class, [
            'id' => $waiter->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_waiter(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $user->restaurant_id]);

        Livewire::actingAs($user)
            ->test(EditWaiter::class, ['record' => $waiter->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(Waiter::class, [
            'id' => $waiter->id,
        ]);
    }

    public function test_scoped_to_user_restaurant(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $userA = User::factory()->restaurant()->withRestaurant($restaurantA)->create();
        $waiterA = Waiter::factory()->create(['restaurant_id' => $restaurantA->id]);

        $restaurantB = Restaurant::factory()->create();
        $userB = User::factory()->restaurant()->withRestaurant($restaurantB)->create();
        $waiterB = Waiter::factory()->create(['restaurant_id' => $restaurantB->id]);

        Livewire::actingAs($userA)
            ->test(ListWaiters::class)
            ->assertCanSeeTableRecords([$waiterA])
            ->assertCanNotSeeTableRecords([$waiterB]);
    }
}
