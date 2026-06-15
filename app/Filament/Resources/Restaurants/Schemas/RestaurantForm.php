<?php

namespace App\Filament\Resources\Restaurants\Schemas;

use App\Models\Restaurant;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RestaurantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('address')
                    ->label('Endereço')
                    ->default(null),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->default(null),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->default(null),
                Toggle::make('is_active')
                    ->label('Ativo')
                    ->default(true),

                Section::make('Administrador do restaurante')
                    ->schema([
                        ToggleButtons::make('admin_mode')
                            ->label('Modo do administrador')
                            ->options([
                                'existing' => 'Selecionar usuário existente',
                                'new' => 'Criar novo usuário',
                            ])
                            ->default('new')
                            ->inline()
                            ->live()
                            ->dehydrated(false),

                        Select::make('admin_user_id')
                            ->label('Administrador existente')
                            ->options(fn (?Restaurant $record): array => User::role('restaurant')
                                ->where(fn ($query) => $query->whereNull('restaurant_id')
                                    ->when($record, fn ($query) => $query->orWhere('restaurant_id', $record->id)))
                                ->pluck('name', 'id')
                                ->toArray())
                            ->searchable()
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'existing')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'existing')
                            ->dehydrated(false),

                        TextInput::make('admin_name')
                            ->label('Nome do administrador')
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->dehydrated(false),

                        TextInput::make('admin_email')
                            ->label('E-mail do administrador')
                            ->email()
                            ->unique('users', 'email', ignoreRecord: false)
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->dehydrated(false),

                        TextInput::make('admin_password')
                            ->label('Senha do administrador')
                            ->password()
                            ->revealable()
                            ->autocomplete('new-password')
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->dehydrated(false),
                    ]),
            ]);
    }
}
