<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouteDay extends Model
{
    use HasFactory;

    protected $table = 'gbs_route_days';

    protected $fillable = ['route_id', 'day_of_week', 'interval_days'];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\RouteDayFactory::new();
    // }
    
    public function route()
    {
        return $this->belongsTo(\Modules\Gbs\Entities\Route::class);
    }

    public function clients()
    {
        return $this->hasMany(\Modules\Gbs\Entities\RouteClient::class, 'route_day_id');
    }
}
