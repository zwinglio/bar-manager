<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

#[Signature('make:user')]
#[Description('Create a new user and assign a Spatie role (admin or restaurant)')]
class MakeUserCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->ask('Name');
        $email = $this->askForUniqueEmail();
        $password = $this->secret('Password (min. 8 characters)');

        while (strlen((string) $password) < 8) {
            $this->warn('Password must be at least 8 characters.');
            $password = $this->secret('Password (min. 8 characters)');
        }

        $roleName = $this->choice('Role', ['admin', 'restaurant'], 'admin');

        $restaurantId = null;
        if ($roleName === 'restaurant') {
            $restaurantId = $this->askForRestaurant();
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'restaurant_id' => $restaurantId,
        ]);

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $user->assignRole($role);

        $this->components->info("User {$user->email} created with role '{$roleName}'.");

        $panel = $roleName === 'admin' ? '/admin' : '/restaurant';
        $this->components->info("Login at: {$panel}");

        return self::SUCCESS;
    }

    private function askForUniqueEmail(): string
    {
        $email = (string) $this->ask('Email address');

        while (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->warn('Please enter a valid email address.');
            $email = (string) $this->ask('Email address');
        }

        while (User::where('email', $email)->exists()) {
            $this->warn('A user with this email already exists.');
            $email = (string) $this->ask('Email address');
        }

        return $email;
    }

    private function askForRestaurant(): ?int
    {
        $restaurants = Restaurant::orderBy('name')->pluck('name', 'id');

        if ($restaurants->isEmpty()) {
            $this->warn('No restaurants found. The user will be created without a restaurant.');

            return null;
        }

        $choice = $this->choice('Restaurant', $restaurants->toArray(), null);

        $id = array_search($choice, $restaurants->toArray(), true);

        return $id !== false ? (int) $id : null;
    }
}
