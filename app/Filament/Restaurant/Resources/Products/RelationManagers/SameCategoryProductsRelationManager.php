<?php

namespace App\Filament\Restaurant\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SameCategoryProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'sameCategoryProducts';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->heading('Produtos na mesma categoria')
            ->modifyQueryUsing(function (Builder $query) {
                $owner = $this->getOwnerRecord();

                if ($owner->product_category_id === null) {
                    return $query->whereRaw('0 = 1');
                }

                return $query->where('id', '!=', $owner->id);
            })
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                IconColumn::make('available')
                    ->label('Disponível')
                    ->boolean(),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
