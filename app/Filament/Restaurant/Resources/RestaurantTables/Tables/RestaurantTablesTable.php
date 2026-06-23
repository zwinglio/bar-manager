<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Tables;

use App\Enums\PaymentMethod;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                TextColumn::make('payment_method')
                    ->label('Pagamento')
                    ->badge()
                    ->formatStateUsing(fn (?PaymentMethod $state): string => $state?->label() ?? '—')
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('status')
                    ->label('Apenas abertas')
                    ->trueLabel('Aberta')
                    ->falseLabel('Fechada')
                    ->placeholder('Todas')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNull('closed_at'),
                        false: fn (Builder $query): Builder => $query->whereNotNull('closed_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
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
                SelectFilter::make('payment_method')
                    ->label('Forma de pagamento')
                    ->options(collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $m) => [$m->value => $m->label()])),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('close')
                    ->visible(fn ($record) => $record->isOpen())
                    ->requiresConfirmation()
                    ->schema([
                        Select::make('payment_method')
                            ->label('Forma de pagamento')
                            ->options(collect(PaymentMethod::cases())->mapWithKeys(fn (PaymentMethod $m) => [$m->value => $m->label()]))
                            ->required()
                            ->native(false),
                    ])
                    ->action(function ($record, array $data) {
                        $record->close(PaymentMethod::from($data['payment_method']));

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
