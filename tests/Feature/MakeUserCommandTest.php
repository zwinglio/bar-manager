<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_admin_user_with_admin_role(): void
    {
        $this->artisan('make:user')
            ->expectsQuestion('Name', 'Admin User')
            ->expectsQuestion('Email address', 'admin@example.com')
            ->expectsQuestion('Password (min. 8 characters)', 'secret123')
            ->expectsChoice('Role', 'admin', ['admin', 'restaurant'])
            ->assertSuccessful();

        $user = User::where('email', 'admin@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->canAccessPanel(app(Panel::class)->id('admin')));
        $this->assertFalse($user->canAccessPanel(app(Panel::class)->id('restaurant')));
    }

    public function test_creates_restaurant_user_with_restaurant_role_and_links_restaurant(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->artisan('make:user')
            ->expectsQuestion('Name', 'Owner User')
            ->expectsQuestion('Email address', 'owner@example.com')
            ->expectsQuestion('Password (min. 8 characters)', 'secret123')
            ->expectsChoice('Role', 'restaurant', ['admin', 'restaurant'])
            ->expectsChoice('Restaurant', $restaurant->name, [$restaurant->name])
            ->assertSuccessful();

        $user = User::where('email', 'owner@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('restaurant'));
        $this->assertEquals($restaurant->id, $user->restaurant_id);
        $this->assertTrue($user->canAccessPanel(app(Panel::class)->id('restaurant')));
        $this->assertFalse($user->canAccessPanel(app(Panel::class)->id('admin')));
    }

    public function test_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->artisan('make:user')
            ->expectsQuestion('Name', 'Dup User')
            ->expectsQuestion('Email address', 'taken@example.com')
            ->expectsOutputToContain('A user with this email already exists.')
            ->expectsQuestion('Email address', 'new@example.com')
            ->expectsQuestion('Password (min. 8 characters)', 'secret123')
            ->expectsChoice('Role', 'admin', ['admin', 'restaurant'])
            ->assertSuccessful();

        $this->assertEquals(2, User::count());
        $this->assertTrue(User::where('email', 'new@example.com')->exists());
    }

    public function test_rejects_short_password(): void
    {
        $this->artisan('make:user')
            ->expectsQuestion('Name', 'Short Pwd')
            ->expectsQuestion('Email address', 'short@example.com')
            ->expectsQuestion('Password (min. 8 characters)', 'short')
            ->expectsOutputToContain('Password must be at least 8 characters.')
            ->expectsQuestion('Password (min. 8 characters)', 'secret123')
            ->expectsChoice('Role', 'admin', ['admin', 'restaurant'])
            ->assertSuccessful();

        $this->assertTrue(User::where('email', 'short@example.com')->exists());
    }
}
