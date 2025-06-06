<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockPurchase extends Model
{
    use HasFactory;
    protected $fillable = ['supplier_id', 'purchase_date', 'total_price'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(StockPurchaseItem::class);
    }

    // StockPurchase.php model
    // app/Models/StockPurchase.php
    // app/Models/StockPurchase.php
    protected static function booted()
    {
        // Hapus event saving, biarkan total_price diisi dari form/page
    }
}
