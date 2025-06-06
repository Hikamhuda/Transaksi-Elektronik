<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Helpers\EncryptHelper;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'price',
        'stock',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncryptHelper::decrypt($value),
            set: fn ($value) => EncryptHelper::encrypt($value)
        );
    }
}
