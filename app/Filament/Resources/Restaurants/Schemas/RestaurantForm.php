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
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('address')
                    ->default(null),
                TextInput::make('phone')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                Toggle::make('is_active')
                    ->default(true),

                Section::make('Restaurant admin')
                    ->schema([
                        ToggleButtons::make('admin_mode')
                            ->options([
                                'existing' => 'Select existing user',
                                'new' => 'Create new user',
                            ])
                            ->default('new')
                            ->inline()
                            ->live()
                            ->dehydrated(false),

                        Select::make('admin_user_id')
                            ->label('Existing admin')
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
                            ->label('Admin name')
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->dehydrated(false),

                        TextInput::make('admin_email')
                            ->label('Admin email')
                            ->email()
                            ->unique('users', 'email', ignoreRecord: false)
                            ->visible(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->required(fn (Get $get): bool => $get('admin_mode') === 'new')
                            ->dehydrated(false),

                        TextInput::make('admin_password')
                            ->label('Admin password')
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
