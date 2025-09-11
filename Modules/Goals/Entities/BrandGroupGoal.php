<?php

namespace Modules\Goals\Entities;

use App\Brands;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandGroupGoal extends Model
{
    protected $table = 'brands_group_goal';

    protected $fillable = [
        'brand_id',
        'group_id',
    ];

    // Relation to Brand
    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }

    // Relation to GoalGroup
    public function goalGroup() 
    {
        return $this->belongsTo(GoalGroup::class, 'group_id');
    }
}
