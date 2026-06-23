<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables\RelationManagers;

use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'restaurantTableProducts';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produto')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label('Qtd')
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Preço unitário')
                    ->money('BRL'),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->state(fn ($record): string => (string) ($record->quantity * $record->unit_price))
                    ->money('BRL'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Adicionar produto')
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
                            ->minValue(1)
                            ->required(),
                        TextInput::make('unit_price')
                            ->label('Preço unitário')
                            ->numeric()
                            ->prefix('R$')
                            ->required(),
                    ])
                    ->visible(fn (): bool => $this->getOwnerRecord()->isOpen()),
            ])
            ->recordActions([
                Action::make('decrement')
                    ->label('')
                    ->tooltip('Diminuir quantidade')
                    ->icon(Heroicon::Minus)
                    ->color('gray')
                    ->visible(fn (): bool => $this->getOwnerRecord()->isOpen())
                    ->action(function ($record) {
                        if ($record->quantity <= 1) {
                            $record->delete();

                            return;
                        }

                        $record->decrement('quantity');
                    }),
                Action::make('increment')
                    ->label('')
                    ->tooltip('Aumentar quantidade')
                    ->icon(Heroicon::Plus)
                    ->color('primary')
                    ->visible(fn (): bool => $this->getOwnerRecord()->isOpen())
                    ->action(fn ($record) => $record->increment('quantity')),
                Action::make('remove')
                    ->label('')
                    ->tooltip('Remover produto')
                    ->icon(Heroicon::XMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (): bool => $this->getOwnerRecord()->isOpen())
                    ->action(fn ($record) => $record->delete()),
            ]);
    }
}
