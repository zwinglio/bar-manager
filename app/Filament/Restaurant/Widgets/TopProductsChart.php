<?php

namespace App\Filament\Restaurant\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 1];

    protected ?string $heading = 'Top 5 produtos (últimos 30 dias)';

    protected function getData(): array
    {
        $rid = Auth::user()?->restaurant_id;

        $results = DB::table('product_restaurant_table as restaurant_table_products')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'restaurant_table_products.restaurant_table_id')
            ->join('products', 'products.id', '=', 'restaurant_table_products.product_id')
            ->where('restaurant_tables.restaurant_id', $rid)
            ->where('restaurant_tables.opened_at', '>=', now()->subDays(30))
            ->selectRaw('products.name as name, SUM(restaurant_table_products.quantity) as qty')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'data' => $results->pluck('qty')->all(),
                    'backgroundColor' => [
                        '#ef4444',
                        '#f97316',
                        '#eab308',
                        '#22c55e',
                        '#3b82f6',
                    ],
                ],
            ],
            'labels' => $results->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
