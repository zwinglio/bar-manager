<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_page_renders(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(ListUsers::class)
            ->assertSuccessful();
    }

    public function test_can_create_user(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'secret123',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(User::class, [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertTrue(
            Hash::check('secret123', User::where('email', 'test@example.com')->first()->password)
        );
    }

    public function test_name_and_email_are_required_on_create(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => null,
                'email' => 'invalid',
                'password' => 'secret123',
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'email' => 'email',
            ])
            ->assertNotNotified();
    }

    public function test_password_is_required_on_create(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(CreateUser::class)
            ->fillForm([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'password' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_can_edit_user_without_changing_password(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $target = User::factory()->create(['name' => 'Target User']);

        Livewire::actingAs($user)
            ->test(EditUser::class, ['record' => $target->id])
            ->fillForm([
                'name' => 'Updated Name',
                'password' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(User::class, [
            'id' => $target->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_can_delete_another_user(): void
    {
        $user = User::factory()->admin()->create();
        $target = User::factory()->create();

        Livewire::actingAs($user)
            ->test(EditUser::class, ['record' => $target->id])
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertDatabaseMissing(User::class, [
            'id' => $target->id,
        ]);
    }

    public function test_cannot_delete_self(): void
    {
        $user = User::factory()->admin()->create();

        Livewire::actingAs($user)
            ->test(EditUser::class, ['record' => $user->id])
            ->assertActionHidden(DeleteAction::class);
    }

    public function test_users_table_shows_roles(): void
    {
        $user = User::factory()->admin()->create();
        $adminUser = User::factory()->admin()->create();
        $restaurantUser = User::factory()->restaurant()->create();

        Livewire::actingAs($user)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$adminUser, $restaurantUser]);
    }

    public function test_can_filter_users_by_role(): void
    {
        $user = User::factory()->admin()->create();
        $adminUser = User::factory()->admin()->create();
        $restaurantUser = User::factory()->restaurant()->create();
        $restaurantRoleId = $restaurantUser->roles->first()->id;

        Livewire::actingAs($user)
            ->test(ListUsers::class)
            ->assertCanSeeTableRecords([$adminUser, $restaurantUser])
            ->filterTable('roles', [$restaurantRoleId])
            ->assertCanSeeTableRecords([$restaurantUser])
            ->assertCanNotSeeTableRecords([$adminUser]);
    }
}
