<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\Schemas;

use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
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
                Section::make('Produtos')
                    ->schema([
                        Repeater::make('restaurantTableProducts')
                            ->relationship()
                            ->defaultItems(0)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produto')
                                    ->relationship(
                                        'product',
                                        'name',
                                        fn ($query) => $query->where('restaurant_id', Auth::user()?->restaurant_id),
                                    )
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, ?int $state) {
                                        $product = Product::find($state);
                                        $set('unit_price', $product?->price);
                                    }),
                                TextInput::make('quantity')
                                    ->label('Quantidade')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                                TextInput::make('unit_price')
                                    ->label('Preço unitário')
                                    ->numeric()
                                    ->prefix('R$')
                                    ->required(),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }
}
