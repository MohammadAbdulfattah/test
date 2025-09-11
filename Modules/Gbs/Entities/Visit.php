<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    use HasFactory;

    protected $table = 'gbs_daily_visits';
    protected $fillable = [
        'user_id',
        'contact_id',
        'started_at',
        'ended_at',
        'route_day_id',
        'user_latitude',
        'user_longitude',
        'reason_id'
    
       
    ];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\VisitesFactory::new();
    // }
}
