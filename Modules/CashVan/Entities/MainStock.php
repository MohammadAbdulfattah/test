<?php

namespace Modules\CashVan\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MainStock extends Model
{
    use HasFactory;

    protected $table = 'main_stock';
    protected $fillable = [
        'unit_price',
        'quantity',
        'variation_id',
        'sub_unit_id',
        'product_id'
    ];
}
