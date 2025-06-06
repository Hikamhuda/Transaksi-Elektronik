<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Helpers\EncryptHelper;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone', 'address'];

    private const IV_DELIMITER = '||';
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncryptHelper::decrypt($value),
            set: fn ($value) => EncryptHelper::encrypt($value)
        );
    }

    /**
     * Phone Attribute
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncryptHelper::decrypt($value),
            set: fn ($value) => EncryptHelper::encrypt($value)
        );
    }

    /**
     * Address Attribute
     */
    protected function address(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncryptHelper::decrypt($value),
            set: fn ($value) => EncryptHelper::encrypt($value)
        );
    }

    /**
     * Relation to Stock Purchases
     */
    public function stockPurchases()
    {
        return $this->hasMany(StockPurchase::class);
    }
}
