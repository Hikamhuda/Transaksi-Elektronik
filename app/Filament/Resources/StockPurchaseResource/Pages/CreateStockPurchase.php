<?php

namespace App\Filament\Resources\StockPurchaseResource\Pages;

use App\Filament\Resources\StockPurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Product;
use App\Models\StockPurchaseItem;

class CreateStockPurchase extends CreateRecord
{
    protected static string $resource = StockPurchaseResource::class;

        protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Pastikan total_price diambil dari form, bukan dihitung ulang dari items kosong
        if (isset($data['total_price'])) {
            // Gunakan total_price dari form jika sudah ada
            return $data;
        }
        $data['total_price'] = collect($data['items'] ?? [])
            ->sum(fn ($item) => (float) $item['quantity'] * (float) $item['price']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Tambah quantity setiap StockPurchaseItem baru ke stok produk terkait
        foreach ($this->record->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->stock += $item->quantity;
                $product->save();
            }
        }
    }

    
}
