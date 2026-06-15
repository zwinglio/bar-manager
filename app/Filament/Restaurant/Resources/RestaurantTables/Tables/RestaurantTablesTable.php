<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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
            ->defaultSort('opened_at', 'desc')
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Aberta',
                        'closed' => 'Fechada',
                    ])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'open' => $query->whereNull('closed_at'),
                        'closed' => $query->whereNotNull('closed_at'),
                        default => $query,
                    }),
                Filter::make('opened_at')
                    ->label('Data de abertura')
                    ->schema([
                        DatePicker::make('from')->label('De'),
                        DatePicker::make('until')->label('Até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q, $date): Builder => $q->whereDate('opened_at', '>=', $date))
                            ->when($data['until'], fn (Builder $q, $date): Builder => $q->whereDate('opened_at', '<=', $date));
                    }),
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
