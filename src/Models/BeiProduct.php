<?php

namespace Emizor\SDK\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class BeiProduct extends Model
{
    use HasFactory;

    protected $table = 'bei_products';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        "bei_account_id",
        "bei_product_code",
        "bei_sin_product_code",
        "bei_activity_code",
        "bei_unit_code",
        "bei_unit_name",
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->id)) {
                $product->id = (string) Str::uuid();
            }
        });
    }
    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return \Emizor\SDK\Database\Factories\BeiProductFactory::new();
    }

}
