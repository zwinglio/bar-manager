<?php

namespace App\Filament\Restaurant\Resources\RestaurantTables;

use App\Filament\Restaurant\Resources\RestaurantTables\Pages\CreateRestaurantTable;
use App\Filament\Restaurant\Resources\RestaurantTables\Pages\EditRestaurantTable;
use App\Filament\Restaurant\Resources\RestaurantTables\Pages\ListRestaurantTables;
use App\Filament\Restaurant\Resources\RestaurantTables\RelationManagers\ProductsRelationManager;
use App\Filament\Restaurant\Resources\RestaurantTables\Schemas\RestaurantTableForm;
use App\Filament\Restaurant\Resources\RestaurantTables\Tables\RestaurantTablesTable;
use App\Models\RestaurantTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RestaurantTableResource extends Resource
{
    protected static ?string $model = RestaurantTable::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|\UnitEnum|null $navigationGroup = 'Operação';

    protected static ?string $navigationLabel = 'Mesas';

    protected static ?string $modelLabel = 'Mesa';

    protected static ?string $pluralModelLabel = 'Mesas';

    public static function form(Schema $schema): Schema
    {
        return RestaurantTableForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantTablesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('restaurant_id', Auth::user()?->restaurant_id);
    }

    public static function getRelations(): array
    {
        return [
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestaurantTables::route('/'),
            'create' => CreateRestaurantTable::route('/create'),
            'edit' => EditRestaurantTable::route('/{record}/edit'),
        ];
    }
}
