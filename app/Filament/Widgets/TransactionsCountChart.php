<?php
namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TransactionsCountChart extends ChartWidget
{
    protected static ?string $heading = 'Jumlah Transaksi per Hari';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Transaction::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Transaksi',
                    'data' => $data->pluck('count'),
                ],
            ],
            'labels' => $data->pluck('date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M')),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
