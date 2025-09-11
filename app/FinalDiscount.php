<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FinalDiscount extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $table = 'final_discount';

    // protected $casts = [
    //     'sub_condition_ids' => 'array',
    //     //'sub_result_ids' => 'array'
    // ];

    public function status_of_smart_root_discounts()
    {
        return $this->hasMany(StatusOfSmartRootDiscount::class);
    }
    public function sub_conditions()
    {
      
        return $this->hasMany(SubCondition::class, 'id', 'sub_condition_ids');
    }

       public function sub_results()
    {
      
        return $this->hasMany(SubResult::class, 'id', 'sub_result_ids');
    }

    
    public function discount_status()
    {
        return $this->belongsTo(DiscountStatus::class, 'discount_status_id');
    }


}
