<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DiscountStatus extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    protected $table = 'discount_status';

}
