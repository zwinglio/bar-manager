<?php

namespace App\Filament\Restaurant\Widgets;

use App\Models\RestaurantTable;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['md' => 1];

    protected ?string $heading = 'Faturamento dos últimos 14 dias';

    protected function getData(): array
    {
        $rid = Auth::user()?->restaurant_id;

        $results = RestaurantTable::where('restaurant_id', $rid)
            ->whereNotNull('closed_at')
            ->where('closed_at', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('DATE(closed_at) as day, SUM(total) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        $labels = [];
        $data = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('d/m');
            $data[] = (float) ($results[$date]?->total ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento (R$)',
                    'data' => $data,
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
