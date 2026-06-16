<?php

namespace App\Filament\Restaurant\Resources\Products\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes')
                    ->schema([
                        Select::make('product_category_id')
                            ->label('Categoria')
                            ->relationship(
                                'category',
                                'name',
                                fn ($query) => $query->where('restaurant_id', Auth::user()?->restaurant_id),
                            )
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nome')
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['restaurant_id'] = Auth::user()->restaurant_id;

                                return ProductCategory::create($data)->id;
                            }),
                        TextInput::make('name')
                            ->label('Nome')
                            ->required(),
                        Textarea::make('description')
                            ->label('Descrição')
                            ->default(null),
                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->nullable(),
                    ]),
                Section::make('Preço')
                    ->schema([
                        TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->required()
                            ->prefix('R$'),
                        TextInput::make('cost')
                            ->label('Custo')
                            ->numeric()
                            ->prefix('R$')
                            ->default(null),
                    ]),
                Section::make('Visibilidade')
                    ->schema([
                        Toggle::make('show_in_menu')
                            ->label('Mostrar no cardápio')
                            ->default(true),
                        Toggle::make('available')
                            ->label('Disponível')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->label('Ordem')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
