<?php

namespace App\Filament\Resources\Restaurants\Pages;

use App\Filament\Resources\Restaurants\RestaurantResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateRestaurant extends CreateRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function afterCreate(): void
    {
        $data = $this->data;

        if ($data['admin_mode'] === 'existing' && filled($data['admin_user_id'] ?? null)) {
            User::whereKey($data['admin_user_id'])->update(['restaurant_id' => $this->record->id]);
        } elseif ($data['admin_mode'] === 'new') {
            $user = User::create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'restaurant_id' => $this->record->id,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('restaurant');
        }
    }
}
