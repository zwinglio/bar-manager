<?php

namespace App\Filament\Restaurant\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductsSoldChart extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 1];

    /** @var array<string, mixed>|null */
    protected ?array $cachedData = null;

    public function getHeading(): string
    {
        $data = $this->cachedData ??= $this->getData();

        $total = collect($data['datasets'][0]['data'] ?? [])->sum();

        return "Produtos vendidos (30 dias): {$total}";
    }

    protected function getData(): array
    {
        $rid = Auth::user()?->restaurant_id;

        $results = DB::table('product_restaurant_table as restaurant_table_products')
            ->join('restaurant_tables', 'restaurant_tables.id', '=', 'restaurant_table_products.restaurant_table_id')
            ->where('restaurant_tables.restaurant_id', $rid)
            ->where('restaurant_tables.opened_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(restaurant_tables.opened_at) as day, SUM(restaurant_table_products.quantity) as qty')
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $data = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d/m');
            $data[] = (int) ($results[$date]?->qty ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Produtos vendidos',
                    'data' => $data,
                    'backgroundColor' => '#ef4444',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
