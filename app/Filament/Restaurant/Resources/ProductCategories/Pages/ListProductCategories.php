<?php

namespace App\Filament\Restaurant\Resources\ProductCategories\Pages;

use App\Filament\Restaurant\Pages\OrderMenu;
use App\Filament\Restaurant\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('order')
                ->label('Ordenar Cardápio')
                ->icon(Heroicon::OutlinedBars3BottomLeft)
                ->url(OrderMenu::getUrl()),
            CreateAction::make(),
        ];
    }
}
