<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatusOfSmartRootDiscount extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $table = 'status_of_smart_root_discount';


    public function smart_root_discount()
    {
        return $this->belongsTo(SmartRootDiscount::class, 'smart_root_discount_id');
    }

    public function final_discount()
    {
        return $this->belongsTo(FinalDiscount::class, 'final_discount_id');
    }

    public function discount_status()
    {
        return $this->belongsTo(DiscountStatus::class, 'discount_status_id');
    }

    
}
