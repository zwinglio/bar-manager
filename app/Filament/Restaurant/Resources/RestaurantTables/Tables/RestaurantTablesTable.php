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
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('waiter.name')
                    ->searchable(),
                TextColumn::make('person_count')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closed_at')
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
                    ->query(fn (Builder $query): Builder => $query->whereNull('closed_at'))
                    ->toggle(),
                SelectFilter::make('waiter_id')
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
