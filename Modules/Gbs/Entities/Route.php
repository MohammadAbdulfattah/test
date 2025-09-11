<?php

namespace Modules\Gbs\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model
{
    use HasFactory;

   
    protected $table = 'gbs_routes'; 

    protected $fillable = [
        'user_id',
        'name',
    ];

    
    // protected static function newFactory()
    // {
    //     return \Modules\Gbs\Database\factories\RouteFactory::new();
    // }
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
    public function clients()
    {
        return $this->belongsToMany(
            \App\Contact::class,      
            'gbs_route_clients',                       
            'route_id',                            
            'contact_id'                               
        );
    }
    
    public function days()
    {
        return $this->hasMany(\Modules\Gbs\Entities\RouteDay::class, 'route_id');
    }
}
