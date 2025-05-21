<?php

namespace App\Filament\Resources\StockPurchaseResource\Pages;

use App\Filament\Resources\StockPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStockPurchase extends EditRecord
{
    protected static string $resource = StockPurchaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
