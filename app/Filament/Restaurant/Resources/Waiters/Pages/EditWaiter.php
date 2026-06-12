<?php

namespace App\Filament\Restaurant\Resources\Waiters\Pages;

use App\Filament\Restaurant\Resources\Waiters\WaiterResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaiter extends EditRecord
{
    protected static string $resource = WaiterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
