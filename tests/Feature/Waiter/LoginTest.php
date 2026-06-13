<?php

namespace Tests\Feature\Waiter;

use App\Models\Restaurant;
use App\Models\Waiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->get(route('waiter.login', ['restaurant' => $restaurant->slug]));

        $response->assertStatus(200);
    }

    public function test_invalid_username_fails(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->post(route('waiter.login.attempt', ['restaurant' => $restaurant->slug]), [
            'username' => 'nonexistent',
        ]);

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurant->slug]));
        $response->assertSessionHasErrors(['username']);
    }

    public function test_valid_username_logs_in(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create([
            'restaurant_id' => $restaurant->id,
            'username' => 'joao',
            'is_active' => true,
        ]);

        $response = $this->post(route('waiter.login.attempt', ['restaurant' => $restaurant->slug]), [
            'username' => 'joao',
        ]);

        $response->assertRedirect(route('waiter.tables.index', ['restaurant' => $restaurant->slug]));
        $this->assertAuthenticated('waiter');
        $this->assertEquals($waiter->id, auth('waiter')->id());
    }

    public function test_inactive_waiter_cannot_login(): void
    {
        $restaurant = Restaurant::factory()->create();
        Waiter::factory()->create([
            'restaurant_id' => $restaurant->id,
            'username' => 'joao',
            'is_active' => false,
        ]);

        $response = $this->post(route('waiter.login.attempt', ['restaurant' => $restaurant->slug]), [
            'username' => 'joao',
        ]);

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurant->slug]));
        $response->assertSessionHasErrors(['username']);
        $this->assertGuest('waiter');
    }

    public function test_cross_restaurant_slug_rejected(): void
    {
        $restaurantA = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create([
            'restaurant_id' => $restaurantA->id,
            'username' => 'joao',
            'is_active' => true,
        ]);

        $restaurantB = Restaurant::factory()->create();

        $this->post(route('waiter.login.attempt', ['restaurant' => $restaurantA->slug]), [
            'username' => 'joao',
        ]);
        $this->assertAuthenticated('waiter');

        $response = $this->get(route('waiter.tables.index', ['restaurant' => $restaurantB->slug]));

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurantB->slug]));
        $this->assertGuest('waiter');
    }

    public function test_logout(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create([
            'restaurant_id' => $restaurant->id,
            'is_active' => true,
        ]);

        $this->actingAs($waiter, 'waiter');

        $response = $this->post(route('waiter.logout', ['restaurant' => $restaurant->slug]));

        $response->assertRedirect(route('waiter.login', ['restaurant' => $restaurant->slug]));
        $this->assertGuest('waiter');
    }
}
