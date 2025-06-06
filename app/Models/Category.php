<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Helpers\EncryptHelper;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => EncryptHelper::decrypt($value),
            set: fn ($value) => EncryptHelper::encrypt($value)
        );
    }
}
