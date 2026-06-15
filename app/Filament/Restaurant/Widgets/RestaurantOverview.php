<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\Product;
use App\Models\RestaurantTable;
use App\Models\Waiter;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RestaurantOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $rid = Auth::user()?->restaurant_id;

        $totalTables = RestaurantTable::where('restaurant_id', $rid)->count();
        $openTables = RestaurantTable::where('restaurant_id', $rid)->whereNull('closed_at')->count();

        $closedTodayTotal = RestaurantTable::where('restaurant_id', $rid)
            ->whereNotNull('closed_at')
            ->whereDate('closed_at', today())
            ->sum('total');

        $openTablesCollection = RestaurantTable::where('restaurant_id', $rid)
            ->whereNull('closed_at')
            ->with('products')
            ->get();

        $openCurrentTotal = $openTablesCollection->sum(
            fn ($table) => (float) $table->current_total
        );

        $revenueToday = (float) $closedTodayTotal + $openCurrentTotal;

        $totalProducts = Product::where('restaurant_id', $rid)->count();
        $availableProducts = Product::where('restaurant_id', $rid)->where('available', true)->count();

        $waiters = Waiter::where('restaurant_id', $rid)->count();

        return [
            Stat::make('Mesas abertas', $openTables)
                ->description("de {$totalTables} mesas no total")
                ->icon('heroicon-o-table-cells'),
            Stat::make('Faturamento hoje', 'R$ '.number_format($revenueToday, 2, ',', '.'))
                ->icon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Produtos disponíveis', $availableProducts)
                ->description("de {$totalProducts} produtos")
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Garçons', $waiters)
                ->icon('heroicon-o-user-group'),
        ];
    }
}
