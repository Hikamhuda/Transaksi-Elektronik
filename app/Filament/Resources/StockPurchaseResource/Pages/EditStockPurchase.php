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

    protected function afterSave(): void
    {
        // Ambil data StockPurchaseItem sebelum update
        $oldItems = $this->getRecord()->items()->withoutGlobalScopes()->get()->keyBy('id');
        $newItems = $this->record->items->keyBy('id');

        // Update stok produk sesuai perubahan quantity
        foreach ($newItems as $id => $newItem) {
            $product = $newItem->product;
            if ($product) {
                $oldQty = $oldItems[$id]->quantity ?? 0;
                $newQty = $newItem->quantity;
                $diff = $newQty - $oldQty;
                $product->stock += $diff;
                $product->save();
            }
        }

        // Kurangi stok produk untuk item yang dihapus
        foreach ($oldItems as $id => $oldItem) {
            if (!$newItems->has($id)) {
                $product = $oldItem->product;
                if ($product) {
                    $product->stock -= $oldItem->quantity;
                    $product->save();
                }
            }
        }
    }
}
