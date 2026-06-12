<?php

namespace App\Filament\Restaurant\Resources\Waiters;

use App\Filament\Restaurant\Resources\Waiters\Pages\CreateWaiter;
use App\Filament\Restaurant\Resources\Waiters\Pages\EditWaiter;
use App\Filament\Restaurant\Resources\Waiters\Pages\ListWaiters;
use App\Filament\Restaurant\Resources\Waiters\Schemas\WaiterForm;
use App\Filament\Restaurant\Resources\Waiters\Tables\WaitersTable;
use App\Models\Waiter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class WaiterResource extends Resource
{
    protected static ?string $model = Waiter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return WaiterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaitersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', Auth::user()?->restaurant_id);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaiters::route('/'),
            'create' => CreateWaiter::route('/create'),
            'edit' => EditWaiter::route('/{record}/edit'),
        ];
    }
}
