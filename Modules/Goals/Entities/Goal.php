<?php

namespace Modules\Goals\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Goal extends Model
{
    protected $table = 'goals';

    protected $fillable = [
        'user_id',
        'created_by',
        'parent_id',
        'category_group_id',
        'products_group_goal_id',
        'brand_group_id',
        'group_id',
        'code',
        'brand_amount',
        'reward_amount',
        'category_amount',
        'product_amount',
        'product_quantity',
    ];

    // Relationships

    // User who owns this goal
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // User who created this goal
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Parent goal (self-referential)
    public function parent()
    {
        return $this->belongsTo(Goal::class, 'parent_id');
    }

    public function child_goals()
    {
        return $this->hasMany(Goal::class, 'parent_id');
    }

    // Category Group Goal relation
    public function categoryGroup()
    {
        return $this->belongsTo(CategoryGroupGoal::class, 'category_group_id');
    }

    // Brand Group Goal relation
    public function brandGroup()
    {
        return $this->belongsTo(BrandGroupGoal::class, 'brand_group_id');
    }
}
