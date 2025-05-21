<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchaseItem extends Model
{
    use HasFactory;
    protected $fillable = ['stock_purchase_id','product_id','quantity','price','subtotal'];

    public function purchase()
    {
        return $this->belongsTo(StockPurchase::class, 'stock_purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

