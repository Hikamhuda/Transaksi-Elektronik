<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\TransactionsCountChart;
use App\Filament\Widgets\TopProductsChart;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    

    public static function getWidgets(): array
    {
        return [
            SalesChart::class,
            // tambahkan widget lain jika ada
        ];
    }

}
