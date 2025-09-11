<?php

namespace Modules\Goals\Entities;

use App\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryGroupGoal extends Model
{
    protected $table = 'categories_group_goal';

    protected $fillable = [
        'category_id',
        'group_id',
    ];

    // Relation to Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relation to GoalGroup
    public function goalGroup()
    {
        return $this->belongsTo(GoalGroup::class, 'group_id');
    }
}
