<?php

namespace App\Filament\Restaurant\Resources\Waiters\Pages;

use App\Filament\Restaurant\Resources\Waiters\WaiterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaiters extends ListRecords
{
    protected static string $resource = WaiterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
