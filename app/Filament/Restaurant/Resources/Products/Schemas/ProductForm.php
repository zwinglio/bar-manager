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
                Section::make('Details')
                    ->schema([
                        Select::make('product_category_id')
                            ->relationship(
                                'category',
                                'name',
                                fn ($query) => $query->where('restaurant_id', Auth::user()?->restaurant_id),
                            )
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $data['restaurant_id'] = Auth::user()->restaurant_id;

                                return ProductCategory::create($data)->id;
                            }),
                        TextInput::make('name')
                            ->required(),
                        Textarea::make('description')
                            ->default(null),
                        FileUpload::make('photo_path')
                            ->image()
                            ->directory('products')
                            ->visibility('public')
                            ->nullable(),
                    ]),
                Section::make('Pricing')
                    ->schema([
                        TextInput::make('price')
                            ->numeric()
                            ->required()
                            ->prefix('R$'),
                        TextInput::make('cost')
                            ->numeric()
                            ->prefix('R$')
                            ->default(null),
                    ]),
                Section::make('Visibility')
                    ->schema([
                        Toggle::make('show_in_menu')
                            ->default(true),
                        Toggle::make('available')
                            ->default(true),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
            ]);
    }
}
