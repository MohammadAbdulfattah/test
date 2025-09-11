<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TypeSmartRootDiscount extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $table = 'type_smart_root_discount';

    public function smart_root_discounts()
    {
        return $this->hasMany(SmartRootDiscount::class);
    }
}
