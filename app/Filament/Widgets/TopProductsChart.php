<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Produk Terlaris (Top 5)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $items = TransactionItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_quantity')
            ->take(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Terjual',
                    'data' => $items->pluck('total_quantity'),
                    'backgroundColor' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                ],
            ],
            'labels' => $items->pluck('product.name'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
