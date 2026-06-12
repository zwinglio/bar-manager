<?php

namespace App\Filament\Restaurant\Resources\Waiters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class WaiterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('username')
                    ->required()
                    ->unique(
                        modifyRuleUsing: fn (Unique $rule) => $rule->where('restaurant_id', Auth::user()?->restaurant_id),
                        ignoreRecord: true,
                    ),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => bcrypt($state)),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
