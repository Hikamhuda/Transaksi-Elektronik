<?php

namespace App\Filament\Resources\StockPurchaseResource\Pages;

use App\Filament\Resources\StockPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStockPurchases extends ListRecords
{
    protected static string $resource = StockPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
