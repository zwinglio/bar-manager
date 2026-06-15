<?php

namespace App\Filament\Restaurant\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto'),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL'),
                IconColumn::make('show_in_menu')
                    ->label('Mostrar no cardápio')
                    ->boolean(),
                IconColumn::make('available')
                    ->label('Disponível')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('product_category_id')
                    ->label('Categoria')
                    ->relationship('category', 'name')
                    ->preload(),
                Filter::make('available')
                    ->label('Disponível')
                    ->query(fn (Builder $query): Builder => $query->where('available', true))
                    ->toggle(),
                Filter::make('show_in_menu')
                    ->label('Mostrar no cardápio')
                    ->query(fn (Builder $query): Builder => $query->where('show_in_menu', true))
                    ->toggle(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
