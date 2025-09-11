<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmartRootDiscount extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $table = 'smart_root_discounts';

    public function business()
    {
        return $this->belongsTo(BusinessLocation::class, 'business_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

      public function created_by_user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function type_smart_root_discount()
    {
        return $this->belongsTo(TypeSmartRootDiscount::class, 'type_smart_root_discount_id');
    }

    public function status_of_smart_root_discounts()
    {
        return $this->hasMany(StatusOfSmartRootDiscount::class);
    }
}
