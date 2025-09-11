<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_date',
        'start_time',
        'end_time',
        'end_latitude',
        'end_longitude',
        'start_latitude',
        'start_longitude',
    ];
    protected $table = 'gbs_shifts';
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\ShiftFactory::new();
    // }

}
