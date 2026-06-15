<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RestaurantTablesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Número')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                TextColumn::make('waiter.name')
                    ->label('Garçom')
                    ->searchable(),
                TextColumn::make('person_count')
                    ->label('Quantidade de pessoas')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Aberta em')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->label('Fechada em')
                    ->dateTime()
                    ->placeholder('—'),
                TextColumn::make('current_total')
                    ->money('BRL')
                    ->label('Total parcial'),
                TextColumn::make('total')
                    ->money('BRL')
                    ->label('Total final'),
            ])
            ->filters([
                Filter::make('open')
                    ->label('Abertas')
                    ->query(fn (Builder $query): Builder => $query->whereNull('closed_at'))
                    ->toggle(),
                SelectFilter::make('waiter_id')
                    ->label('Garçom')
                    ->relationship('waiter', 'name')
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('close')
                    ->visible(fn ($record) => $record->isOpen())
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->close();

                        Notification::make()
                            ->success()
                            ->title('Mesa fechada com sucesso.')
                            ->send();
                    })
                    ->icon(Heroicon::OutlinedCheckCircle),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
