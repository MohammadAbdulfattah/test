<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GbsFailureReason extends Model
{
    use HasFactory;

   
    protected $fillable = ['reason'];
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\GbsFailureReasonFactory::new();
    // }
}
