<?php

namespace App\Filament\Resources\StockPurchaseResource\Pages;

use App\Filament\Resources\StockPurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;

class CreateStockPurchase extends CreateRecord
{
    protected static string $resource = StockPurchaseResource::class;

    protected function afterCreate(): void
    {
        foreach ($this->record->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }
    }
}
