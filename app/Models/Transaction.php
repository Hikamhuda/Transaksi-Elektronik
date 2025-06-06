<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_price',
        'paid_amount',
        'change',
        'payment_method',
    ];

    protected function payment_method(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => self::decryptValue($value),
            set: fn ($value) => self::encryptValue($value)
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
}
