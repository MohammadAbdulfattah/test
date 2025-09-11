<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reason extends Model
{
    use HasFactory;
    protected $table = 'gbs_failure_reasons'; 
    protected $fillable = ['reason'];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\ReasonFactory::new();
    // }
}
