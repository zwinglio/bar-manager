<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\RestaurantTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;

class OpenTablesTable extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Mesas abertas';

    public function table(Table $table): Table
    {
        $rid = Auth::user()?->restaurant_id;

        return $table
            ->query(
                RestaurantTable::query()
                    ->where('restaurant_id', $rid)
                    ->whereNull('closed_at')
                    ->with('waiter')
                    ->orderBy('opened_at')
            )
            ->columns([
                TextColumn::make('number')
                    ->label('Mesa')
                    ->sortable(),
                TextColumn::make('waiter.name')
                    ->label('Garçom')
                    ->searchable(),
                TextColumn::make('person_count')
                    ->label('Pessoas')
                    ->sortable(),
                TextColumn::make('opened_at')
                    ->label('Aberta há')
                    ->state(fn (RestaurantTable $record): string => $record->opened_at?->diffForHumans() ?? '—'),
                TextColumn::make('current_total')
                    ->label('Total parcial')
                    ->state(fn (RestaurantTable $record): string => 'R$ '.number_format((float) $record->current_total, 2, ',', '.')),
            ]);
    }
}
