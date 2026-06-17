<?php

namespace Tests\Feature\Filament;

use App\Filament\Restaurant\Pages\RestaurantSettings;
use App\Models\Restaurant;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RestaurantSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Filament::setCurrentPanel('restaurant');
    }

    public function test_settings_page_renders(): void
    {
        $user = User::factory()->restaurant()->withRestaurant()->create();

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->assertSuccessful();
    }

    public function test_shows_restaurant_data_in_infolist(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Meu Restaurante',
            'slug' => 'meu-restaurante',
            'address' => 'Rua das Flores, 123',
            'phone' => '555-1234',
            'email' => 'contato@restaurante.com',
        ]);
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->assertSee('Meu Restaurante')
            ->assertSee('meu-restaurante')
            ->assertSee('Rua das Flores, 123')
            ->assertSee('555-1234')
            ->assertSee('contato@restaurante.com');
    }

    public function test_shows_empty_optional_fields_with_placeholder(): void
    {
        $restaurant = Restaurant::factory()->create([
            'address' => null,
            'phone' => null,
            'email' => null,
        ]);
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->assertSee($restaurant->name)
            ->assertSee('Endereço')
            ->assertSee('Telefone')
            ->assertSee('E-mail')
            ->assertSee('—');
    }

    public function test_can_update_restaurant_settings_via_edit_action(): void
    {
        $restaurant = Restaurant::factory()->create([
            'name' => 'Old Name',
            'address' => 'Old Address',
            'phone' => 'Old Phone',
            'email' => 'old@example.com',
        ]);
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->callAction('edit', data: [
                'name' => 'New Name',
                'address' => 'New Address',
                'phone' => 'New Phone',
                'email' => 'new@example.com',
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertDatabaseHas(Restaurant::class, [
            'id' => $restaurant->id,
            'name' => 'New Name',
            'address' => 'New Address',
            'phone' => 'New Phone',
            'email' => 'new@example.com',
        ]);
    }

    public function test_can_upload_logo_via_edit_action(): void
    {
        Storage::fake('public');

        $restaurant = Restaurant::factory()->create();
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->callAction('edit', data: [
                'name' => $restaurant->name,
                'logo_path' => [UploadedFile::fake()->image('logo.png')],
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        $this->assertNotNull($restaurant->fresh()->logo_path);
        Storage::disk('public')->assertExists($restaurant->fresh()->logo_path);
    }

    public function test_name_is_required_in_edit_action(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => 'Test']);
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        Livewire::actingAs($user)
            ->test(RestaurantSettings::class)
            ->callAction('edit', data: [
                'name' => null,
            ])
            ->assertHasFormErrors([
                'name' => 'required',
            ])
            ->assertNotNotified();
    }

    public function test_scoped_to_current_users_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create(['name' => 'Owner Restaurant']);
        $otherRestaurant = Restaurant::factory()->create(['name' => 'Other Restaurant']);
        $user = User::factory()->restaurant()->create(['restaurant_id' => $restaurant->id]);

        $component = Livewire::actingAs($user)
            ->test(RestaurantSettings::class);

        $this->assertEquals($restaurant->id, $component->instance()->getRecord()?->id);
        $this->assertNotEquals($otherRestaurant->id, $component->instance()->getRecord()?->id);
    }
}
