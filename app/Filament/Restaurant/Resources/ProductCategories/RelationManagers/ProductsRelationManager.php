<?php

namespace App\Filament\Restaurant\Resources\ProductCategories\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detalhes')
                    ->schema([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto'),
                TextColumn::make('name')
                    ->label('Nome')
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
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['restaurant_id'] = Auth::user()->restaurant_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
