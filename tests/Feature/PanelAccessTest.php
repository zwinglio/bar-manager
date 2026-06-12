<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_cannot_access_restaurant_panel(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get('/restaurant')
            ->assertForbidden();
    }

    public function test_restaurant_can_access_restaurant_panel(): void
    {
        $user = User::factory()->restaurant()->create();

        $this->actingAs($user)
            ->get('/restaurant')
            ->assertOk();
    }

    public function test_restaurant_cannot_access_admin_panel(): void
    {
        $user = User::factory()->restaurant()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_user_without_role_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_user_without_role_cannot_access_restaurant_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/restaurant')
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_for_admin_panel(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_to_login_for_restaurant_panel(): void
    {
        $this->get('/restaurant')
            ->assertRedirect('/restaurant/login');
    }
}
