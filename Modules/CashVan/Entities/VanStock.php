<?php

namespace Modules\CashVan\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VanStock extends Model
{
    use HasFactory;
    protected $table = 'van_stock';
    protected $fillable = [
        'product_variation_id',
        'qty_available',
        'van_id',
        'variation_id',
        'product_id'
    ];
    
   
}
