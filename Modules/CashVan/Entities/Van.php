<?php

namespace Modules\CashVan\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Van extends Model
{
    use HasFactory;
    protected $fillable=[
        'driver_id',
        'color',
        'license_plate',
        'name',
        'business_id'
    ];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function van_locations(){
        return $this->belongsToMany(\App\BusinessLocation::class, 'van_locations', 'van_id', 'business_location_id');
    }

    public function van_stock(){
        return $this->hasMany(VanStock::class);
    }
}
