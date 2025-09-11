<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RouteClient extends Model
{
    use HasFactory;

    protected $table = 'gbs_route_clients';

    protected $fillable = ['route_day_id', 'contact_id'];
    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\RouteClientFactory::new();
    // }
    public function routeDay()
    {
        return $this->belongsTo(\Modules\Gbs\Entities\RouteDay::class, 'route_day_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Contact::class, 'contact_id');
    }

}
