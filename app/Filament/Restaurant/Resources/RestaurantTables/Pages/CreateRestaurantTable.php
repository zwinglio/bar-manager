<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Pages;

use App\Filament\Restaurant\Resources\RestaurantTables\RestaurantTableResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateRestaurantTable extends CreateRecord
{
    protected static string $resource = RestaurantTableResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = Auth::user()->restaurant_id;

        return $data;
    }
}
