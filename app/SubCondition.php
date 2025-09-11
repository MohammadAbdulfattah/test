<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubCondition extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $table = 'sub_condition';

        public function varition()
    {
        return $this->belongsTo(Variation::class, 'product_id');
    }

          public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

}
