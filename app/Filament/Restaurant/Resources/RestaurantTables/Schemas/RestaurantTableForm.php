<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class RestaurantTableForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Mesa')
                    ->schema([
                        TextInput::make('number')
                            ->label('Número')
                            ->numeric()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nome')
                            ->default(null),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->default(null),
                        TextInput::make('person_count')
                            ->label('Quantidade de pessoas')
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ]),
                Section::make('Atendimento')
                    ->schema([
                        Select::make('waiter_id')
                            ->label('Garçom')
                            ->relationship(
                                'waiter',
                                'name',
                                fn ($query) => $query->where('restaurant_id', Auth::user()?->restaurant_id),
                            )
                            ->preload()
                            ->nullable(),
                        TextInput::make('opened_at')
                            ->label('Aberta em')
                            ->default(now()->toDateTimeLocalString())
                            ->required(),
                        TextInput::make('closed_at')
                            ->label('Fechada em')
                            ->disabled()
                            ->default(null),
                    ]),
            ]);
    }
}
