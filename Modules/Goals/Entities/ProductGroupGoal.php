<?php

namespace Modules\Goals\Entities;

use App\Variation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductGroupGoal extends Model
{
    protected $table = 'products_group_goal';

    protected $fillable = [
        'variation_id',
        'group_id',
    ];

    // Optional: Define relationship to Variation
    public function variation()
    {
        return $this->belongsTo(Variation::class, 'variation_id');
    }

    // Optional: Define relationship to GoalGroup
    public function goalGroup()
    {
        return $this->belongsTo(GoalGroup::class, 'group_id');
    }
}
