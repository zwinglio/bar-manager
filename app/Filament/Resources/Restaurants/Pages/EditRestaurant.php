<?php

namespace App\Filament\Resources\Restaurants\Pages;

use App\Filament\Resources\Restaurants\RestaurantResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;

class EditRestaurant extends EditRecord
{
    protected static string $resource = RestaurantResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $admin = $this->record->admin;

        if ($admin) {
            $data['admin_mode'] = 'existing';
            $data['admin_user_id'] = $admin->id;
        } else {
            $data['admin_mode'] = 'new';
            $data['admin_user_id'] = null;
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->data;
        $oldAdmin = $this->record->admin;

        if ($data['admin_mode'] === 'existing' && filled($data['admin_user_id'] ?? null)) {
            $newAdminId = (int) $data['admin_user_id'];

            if ($oldAdmin && $oldAdmin->id !== $newAdminId) {
                $oldAdmin->update(['restaurant_id' => null]);
            }

            if (! $oldAdmin || $oldAdmin->id !== $newAdminId) {
                User::whereKey($newAdminId)->update(['restaurant_id' => $this->record->id]);
            }
        } elseif ($data['admin_mode'] === 'new') {
            if ($oldAdmin) {
                $oldAdmin->update(['restaurant_id' => null]);
            }

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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
