<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchaseItem extends Model
{
    use HasFactory;
    protected $fillable = ['stock_purchase_id', 'product_id', 'quantity', 'price', 'subtotal'];

    public function stockPurchase()
    {
        return $this->belongsTo(StockPurchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // app/Models/StockPurchaseItem.php
    // app/Models/StockPurchaseItem.php
    protected static function booted()
    {
        static::saving(function (StockPurchaseItem $item) {
            $item->subtotal = $item->quantity * $item->price;
        });
    }

    // StockPurchase.php
    public function items()
    {
        return $this->hasMany(StockPurchaseItem::class);
    }

    // StockPurchaseItem.php
    // Duplicate product() method removed.
}
