<?php

namespace App\Filament\Restaurant\Resources\Waiters\Pages;

use App\Filament\Restaurant\Resources\Waiters\WaiterResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateWaiter extends CreateRecord
{
    protected static string $resource = WaiterResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['restaurant_id'] = Auth::user()->restaurant_id;

        return $data;
    }
}
